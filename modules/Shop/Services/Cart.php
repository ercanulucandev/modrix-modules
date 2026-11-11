<?php
namespace App\Modules\Shop\Services;

use Core\Database;
use Core\HttpException;
use Core\Session;
use Core\Setting;

// Sepet: oturumda [anahtar => [ürün, varyant, adet]]; fiyatlar her seferinde veritabanından okunur.
// Üst kısımdaki sepet sayısı 'modrix_cart' çerezinden JS ile yazılır (sayfa önbelleğiyle uyumlu).
final class Cart
{
    private const KEY = 'shop_cart';
    public const MAX_QTY = 99;

    public static function add(int $productId, ?int $variantId, int $qty): void
    {
        $product = Shop::product($productId);
        if (!$product || $product['status'] !== 'published') throw new HttpException(404, 'Ürün bulunamadı.');
        $variants = Shop::variants($productId);
        if ($variants && !$variantId) throw new HttpException(422, 'Lütfen bir seçenek seçin.');
        if ($variantId && !in_array($variantId, array_map('intval', array_column($variants, 'id')), true)) throw new HttpException(422, 'Seçenek bulunamadı.');

        $cart = self::raw();
        $key = $productId . ':' . ($variantId ?? 0);
        $new = min(self::MAX_QTY, ($cart[$key]['q'] ?? 0) + max(1, $qty));
        self::checkStock($product, $variantId, $new);
        $cart[$key] = ['p' => $productId, 'v' => $variantId, 'q' => $new];
        self::save($cart);
    }

    public static function update(string $key, int $qty): void
    {
        $cart = self::raw();
        if (!isset($cart[$key])) return;
        if ($qty <= 0) {
            unset($cart[$key]);
        } else {
            $qty = min(self::MAX_QTY, $qty);
            $product = Shop::product($cart[$key]['p']);
            if ($product) self::checkStock($product, $cart[$key]['v'], $qty);
            $cart[$key]['q'] = $qty;
        }
        self::save($cart);
    }

    public static function clear(): void
    {
        self::save([]);
    }

    // Satırlar ve toplamlar. Satıştan kalkmış ürünler sepetten düşer.
    //   ['lines' => [['key', 'product', 'variant', 'name', 'price', 'qty', 'total', 'image', 'url', 'stock_ok']], 'subtotal', 'shipping', 'count']
    public static function contents(): array
    {
        $cart = self::raw();
        $lines = [];
        $subtotal = 0;
        $changed = false;
        foreach ($cart as $key => $item) {
            $product = Shop::product((int) $item['p']);
            $variant = $item['v'] ? Database::table('shop_variants')->where('id', $item['v'])->where('product_id', $item['p'])->first() : null;
            if (!$product || $product['status'] !== 'published' || ($item['v'] && !$variant)) {
                unset($cart[$key]);
                $changed = true;
                continue;
            }
            $price = Shop::cents($variant['price'] ?? $product['price']);
            $stock = $variant ? $variant['stock'] : $product['stock'];
            $lines[] = [
                'key'      => $key,
                'product'  => $product,
                'variant'  => $variant,
                'name'     => $product['name'] . ($variant ? ' – ' . $variant['name'] : ''),
                'sku'      => $variant['sku'] ?? $product['sku'],
                'price'    => $price,
                'qty'      => (int) $item['q'],
                'total'    => $price * (int) $item['q'],
                'image'    => Shop::image($product),
                'stock_ok' => $stock === null || (int) $stock >= (int) $item['q'],
                'stock'    => $stock,
            ];
            $subtotal += $price * (int) $item['q'];
        }
        if ($changed) self::save($cart);
        return ['lines' => $lines, 'subtotal' => $subtotal, 'shipping' => self::shipping($subtotal), 'count' => array_sum(array_column($lines, 'qty'))];
    }

    // Kargo (kuruş): sabit ücret, belirli tutarın üstü ücretsiz
    public static function shipping(int $subtotal): int
    {
        $fee = Shop::cents(Setting::get('shop_shipping_fee', 0));
        $free = Setting::get('shop_free_over');
        if ($subtotal === 0 || ($free !== null && $free !== '' && $subtotal >= Shop::cents($free))) return 0;
        return $fee;
    }

    public static function count(): int
    {
        return array_sum(array_column(self::raw(), 'q'));
    }

    private static function checkStock(array $product, ?int $variantId, int $qty): void
    {
        $stock = $variantId ? Database::table('shop_variants')->where('id', $variantId)->value('stock') : $product['stock'];
        if ($stock !== null && (int) $stock < $qty) {
            throw new HttpException(422, (int) $stock > 0 ? "Stokta en fazla $stock adet var." : 'Bu ürün tükendi.');
        }
    }

    private static function raw(): array
    {
        return (array) Session::get(self::KEY, []);
    }

    private static function save(array $cart): void
    {
        Session::set(self::KEY, $cart);
        $count = array_sum(array_column($cart, 'q'));
        if (!headers_sent()) setcookie('modrix_cart', (string) $count, ['expires' => $count ? time() + 86400 * 30 : time() - 3600, 'path' => '/', 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS'])]);
    }
}
