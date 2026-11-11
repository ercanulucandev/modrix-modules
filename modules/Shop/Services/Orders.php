<?php
namespace App\Modules\Shop\Services;

use Core\Database;
use Core\Hook;
use Core\HttpException;
use Core\Log;
use Core\Mail;
use Core\Request;
use Core\Router;
use Core\Setting;
use Core\View;

// Siparişler: oluşturma (stok düşer), ödeme alındı, durum değişikliği (iptalde stok geri döner), e-postalar
final class Orders
{
    public static function create(array $customer, string $method): array
    {
        $cart = Cart::contents();
        if (!$cart['lines']) throw new HttpException(422, 'Sepetiniz boş.');
        $fee = $method === 'cod' ? Shop::cents(Setting::get('shop_cod_fee', 0)) : 0;
        $total = $cart['subtotal'] + $cart['shipping'] + $fee;

        $order = Database::transaction(function () use ($cart, $customer, $method, $fee, $total) {
            // Stok: sadece yeterliyse düşer (aynı anda iki sipariş aynı son ürünü alamasın)
            foreach ($cart['lines'] as $l) {
                $table = $l['variant'] ? 'shop_variants' : 'shop_products';
                $id = $l['variant']['id'] ?? $l['product']['id'];
                $stock = Database::table($table)->where('id', $id)->value('stock');
                if ($stock === null) continue;
                $ok = Database::query("UPDATE $table SET stock = stock - ? WHERE id = ? AND stock >= ?", [$l['qty'], $id, $l['qty']])->rowCount();
                if (!$ok) throw new HttpException(422, "\"{$l['name']}\" için yeterli stok kalmadı. Sepetinizi güncelleyin.");
            }

            $id = Database::table('shop_orders')->insertGetId([
                'token'          => bin2hex(random_bytes(16)),
                'status'         => $method === 'cod' ? 'processing' : 'pending',
                'payment_method' => $method,
                'customer_name'  => $customer['name'],
                'email'          => mb_strtolower($customer['email']),
                'phone'          => $customer['phone'],
                'address'        => json_encode(['city' => $customer['city'], 'district' => $customer['district'], 'line' => $customer['address'], 'zip' => $customer['zip'] ?? ''], JSON_UNESCAPED_UNICODE),
                'billing'        => ($customer['company'] ?? '') !== '' ? json_encode(['company' => $customer['company'], 'tax_office' => $customer['tax_office'] ?? '', 'tax_number' => $customer['tax_number'] ?? ''], JSON_UNESCAPED_UNICODE) : null,
                'note'           => $customer['note'] ?? null,
                'subtotal'       => Shop::fromCents($cart['subtotal']),
                'shipping_fee'   => Shop::fromCents($cart['shipping']),
                'payment_fee'    => Shop::fromCents($fee),
                'total'          => Shop::fromCents($total),
                'ip'             => Request::ip(),
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);
            // Sipariş no: yıl + sıra (ör. 26-000123)
            Database::table('shop_orders')->where('id', $id)->update(['number' => date('y') . '-' . str_pad((string) $id, 6, '0', STR_PAD_LEFT)]);
            foreach ($cart['lines'] as $l) {
                Database::table('shop_order_items')->insert([
                    'order_id' => $id, 'product_id' => $l['product']['id'], 'variant_id' => $l['variant']['id'] ?? null, 'name' => $l['name'], 'sku' => $l['sku'],
                    'price' => Shop::fromCents($l['price']), 'quantity' => $l['qty'], 'total' => Shop::fromCents($l['total']),
                ]);
            }
            return self::find($id);
        });

        Cart::clear();
        Hook::fire('shop.order.created', $order);
        self::mailCustomer($order, 'created');
        self::mailAdmin($order);
        return $order;
    }

    public static function find(int $id): ?array
    {
        $o = Database::table('shop_orders')->where('id', $id)->first();
        return $o ? self::decode($o) : null;
    }

    public static function findByToken(string $token): ?array
    {
        if (!preg_match('/^[a-f0-9]{32}$/', $token)) return null;
        $o = Database::table('shop_orders')->where('token', $token)->first();
        return $o ? self::decode($o) : null;
    }

    public static function items(int $orderId): array
    {
        return Database::table('shop_order_items')->where('order_id', $orderId)->orderBy('id')->get();
    }

    // Ödeme alındı (kart bildirimi ya da admin "ödeme alındı"). İkinci kez çağrılırsa bir şey yapmaz.
    public static function markPaid(array $order, ?string $ref = null): void
    {
        if ($order['payment_status'] === 'paid') return;
        Database::table('shop_orders')->where('id', $order['id'])->update([
            'payment_status' => 'paid', 'payment_ref' => $ref ?? $order['payment_ref'], 'paid_at' => date('Y-m-d H:i:s'),
            'status' => $order['status'] === 'pending' ? 'processing' : $order['status'], 'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $order = self::find($order['id']);
        Hook::fire('shop.order.paid', $order);
        self::mailCustomer($order, 'paid');
    }

    public static function markFailed(array $order): void
    {
        if ($order['payment_status'] !== 'paid') Database::table('shop_orders')->where('id', $order['id'])->update(['payment_status' => 'failed', 'updated_at' => date('Y-m-d H:i:s')]);
    }

    // Durum değişikliği (admin). İptal/iade: stok geri eklenir.
    public static function setStatus(array $order, string $status, array $extra = [], bool $notify = false): array
    {
        if (!isset(Shop::STATUSES[$status])) throw new HttpException(422, 'Geçersiz durum.');
        $restock = in_array($status, ['cancelled', 'refunded'], true) && !in_array($order['status'], ['cancelled', 'refunded'], true);
        Database::transaction(function () use ($order, $status, $extra, $restock) {
            if ($restock) {
                foreach (self::items($order['id']) as $i) {
                    $table = $i['variant_id'] ? 'shop_variants' : 'shop_products';
                    Database::query("UPDATE $table SET stock = stock + ? WHERE id = ? AND stock IS NOT NULL", [$i['quantity'], $i['variant_id'] ?: $i['product_id']]);
                }
            }
            Database::table('shop_orders')->where('id', $order['id'])->update(['status' => $status, 'updated_at' => date('Y-m-d H:i:s')] + $extra
                + ($status === 'refunded' && $order['payment_status'] === 'paid' ? ['payment_status' => 'refunded'] : []));
        });
        $updated = self::find($order['id']);
        Hook::fire('shop.order.status', $updated, $order['status']);
        if ($notify) self::mailCustomer($updated, 'status');
        return $updated;
    }

    public static function url(array $order): string
    {
        return Shop::absolute(Router::url('shop.order', ['token' => $order['token']]));
    }

    // ---- E-postalar

    public static function mailCustomer(array $order, string $event): void
    {
        $site = (string) Setting::get('site_name', 'Mağaza');
        $subject = match ($event) {
            'created' => "Siparişiniz alındı #{$order['number']}",
            'paid'    => "Ödemeniz alındı #{$order['number']}",
            default   => "Siparişiniz: " . Shop::STATUSES[$order['status']] . " #{$order['number']}",
        };
        $html = (string) View::render('Shop::emails/order', ['order' => $order, 'items' => self::items($order['id']), 'event' => $event, 'site' => $site, 'url' => self::url($order)]);
        $text = strip_tags(str_replace(['<br>', '</p>', '</tr>'], "\n", $html));
        if (!Mail::send($order['email'], "$subject · $site", html_entity_decode(preg_replace("/\n\s+/", "\n", $text)), ['html' => $html])) {
            Log::warning('Sipariş e-postası gönderilemedi', ['order' => $order['number'], 'error' => Mail::lastError()]);
        }
    }

    private static function mailAdmin(array $order): void
    {
        $to = (string) (Setting::get('shop_notify_email') ?: Setting::get('contact_email'));
        if ($to === '') return;
        $lines = array_map(fn($i) => "- {$i['name']} × {$i['quantity']}  " . Shop::money($i['total']), self::items($order['id']));
        Mail::send($to, "Yeni sipariş #{$order['number']} · " . Shop::money($order['total']),
            "Yeni sipariş: #{$order['number']}\nMüşteri: {$order['customer_name']} ({$order['email']}, {$order['phone']})\nÖdeme: " . Shop::methodLabel($order['payment_method'])
            . "\n\n" . implode("\n", $lines) . "\n\nToplam: " . Shop::money($order['total']) . "\n\nPanel: " . Shop::absolute(Request::url("admin/shop/orders/{$order['id']}")) . "\n",
            ['reply_to' => $order['email']]);
    }

    private static function decode(array $o): array
    {
        $o['address'] = json_decode((string) $o['address'], true) ?: [];
        $o['billing'] = json_decode((string) $o['billing'], true) ?: [];
        return $o;
    }
}
