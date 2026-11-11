<?php
namespace App\Modules\Shop\Services;

use Core\Log;
use Core\Request;
use Core\Router;
use Core\Setting;
use RuntimeException;

// iyzico ortak ödeme sayfası (Checkout Form). SDK gerekmez: istekler IYZWSv2 imzasıyla.
//   1) start(): ödeme formunu başlatır, müşteri iyzico sayfasına gider
//   2) iyzico müşteriyi callbackUrl'e (POST token) döndürür -> verify(): sonucu sorgular
final class Iyzico
{
    public static function start(array $order, array $items): string
    {
        $address = $order['address'];
        [$first, $last] = self::splitName($order['customer_name']);
        $basket = [];
        foreach ($items as $i) {
            $basket[] = ['id' => (string) ($i['product_id'] ?? $i['id']), 'name' => mb_substr($i['name'], 0, 100), 'category1' => 'Ürün', 'itemType' => 'PHYSICAL', 'price' => $i['total']];
        }
        $extra = Shop::cents($order['shipping_fee']) + Shop::cents($order['payment_fee']);
        if ($extra > 0) $basket[] = ['id' => 'kargo', 'name' => 'Kargo', 'category1' => 'Kargo', 'itemType' => 'VIRTUAL', 'price' => Shop::fromCents($extra)];
        $addr = ['contactName' => $order['customer_name'], 'city' => $address['city'], 'country' => 'Turkey', 'address' => trim($address['line'] . ' ' . $address['district']), 'zipCode' => $address['zip'] ?: null];

        $res = self::request('/payment/iyzipos/checkoutform/initialize/auth/ecom', [
            'locale'              => 'tr',
            'conversationId'      => $order['number'],
            'price'               => self::amount($order['total']),
            'paidPrice'           => self::amount($order['total']),
            'currency'            => 'TRY',
            'basketId'            => $order['number'],
            'paymentGroup'        => 'PRODUCT',
            'callbackUrl'         => Shop::absolute(Router::url('shop.iyzico.callback', ['token' => $order['token']])),
            'enabledInstallments' => [1, 2, 3, 6, 9],
            'buyer'               => [
                'id' => 'm' . $order['id'], 'name' => $first, 'surname' => $last, 'gsmNumber' => $order['phone'], 'email' => $order['email'],
                'identityNumber' => '11111111111',   // TC kimlik zorunlu alan; istenmiyorsa bu varsayılan kabul edilir
                'registrationAddress' => $addr['address'], 'ip' => Request::ip() ?: '127.0.0.1', 'city' => $address['city'], 'country' => 'Turkey',
            ],
            'shippingAddress'     => $addr,
            'billingAddress'      => $addr,
            'basketItems'         => array_map(fn($b) => ['price' => self::amount($b['price'])] + $b, $basket),
        ]);
        if (($res['status'] ?? '') !== 'success' || empty($res['paymentPageUrl'])) {
            Log::warning('iyzico ödeme başlatılamadı', ['order' => $order['number'], 'error' => $res['errorMessage'] ?? null]);
            throw new RuntimeException('Kartla ödeme başlatılamadı: ' . ($res['errorMessage'] ?? 'bilinmeyen hata'));
        }
        return $res['paymentPageUrl'];
    }

    // Dönüş: ['ok' => bool, 'ref' => paymentId, 'message' => ...]
    public static function verify(array $order, string $token): array
    {
        $res = self::request('/payment/iyzipos/checkoutform/auth/ecom/detail', ['locale' => 'tr', 'conversationId' => $order['number'], 'token' => $token]);
        $ok = ($res['status'] ?? '') === 'success' && ($res['paymentStatus'] ?? '') === 'SUCCESS'
            && ($res['basketId'] ?? '') === $order['number'] && Shop::cents($res['paidPrice'] ?? 0) >= Shop::cents($order['total']);
        return ['ok' => $ok, 'ref' => (string) ($res['paymentId'] ?? ''), 'message' => $res['errorMessage'] ?? null];
    }

    // IYZWSv2: Authorization = "IYZWSv2 " + base64("apiKey:..&randomKey:..&signature:" + hex(HMAC-SHA256(rnd + yol + gövde, gizli)))
    private static function request(string $path, array $body): array
    {
        $key = (string) Setting::get('shop_iyzico_key');
        $secret = (string) Setting::get('shop_iyzico_secret');
        $base = Setting::bool('shop_iyzico_sandbox') ? 'https://sandbox-api.iyzipay.com' : 'https://api.iyzipay.com';
        $json = json_encode(self::clean($body), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $rnd = (string) (hrtime(true)) . random_int(100, 999);
        $signature = hash_hmac('sha256', $rnd . $path . $json, $secret);
        $auth = 'IYZWSv2 ' . base64_encode("apiKey:$key&randomKey:$rnd&signature:$signature");

        $ch = curl_init($base . $path);
        curl_setopt_array($ch, [
            CURLOPT_POST => true, CURLOPT_POSTFIELDS => $json, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json', "Authorization: $auth", "x-iyzi-rnd: $rnd"],
        ]);
        $raw = curl_exec($ch);
        $error = curl_error($ch);
        if ($raw === false) throw new RuntimeException('iyzico\'ya bağlanılamadı: ' . $error);
        return json_decode($raw, true) ?: ['status' => 'failure', 'errorMessage' => 'Geçersiz yanıt'];
    }

    private static function amount(string|float $value): string
    {
        // iyzico "10.0" / "10.5" biçimini bekler
        $s = rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
        return str_contains($s, '.') ? $s : "$s.0";
    }

    private static function clean(array $a): array
    {
        return array_map(fn($v) => is_array($v) ? self::clean($v) : $v, array_filter($a, fn($v) => $v !== null));
    }

    private static function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name));
        $last = count($parts) > 1 ? array_pop($parts) : '-';
        return [implode(' ', $parts) ?: $name, $last];
    }
}
