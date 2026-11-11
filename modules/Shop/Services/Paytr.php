<?php
namespace App\Modules\Shop\Services;

use Core\Log;
use Core\Request;
use Core\Router;
use Core\Setting;
use RuntimeException;

// PayTR iFrame API. 1) token(): ödeme çerçevesi için token  2) PayTR sunucusu Bildirim URL'ine (/api/shop/paytr/notify) sonucu yollar
final class Paytr
{
    public static function token(array $order, array $items): string
    {
        $merchant = (string) Setting::get('shop_paytr_merchant');
        $key = (string) Setting::get('shop_paytr_key');
        $salt = (string) Setting::get('shop_paytr_salt');
        $test = Setting::bool('shop_paytr_sandbox') ? '1' : '0';

        $oid = 'S' . $order['id'] . 'T' . time();                     // sadece harf/rakam; her denemede yeni
        $amount = (string) Shop::cents($order['total']);
        $basket = base64_encode(json_encode(array_map(fn($i) => [mb_substr($i['name'], 0, 100), number_format((float) $i['price'], 2, '.', ''), (int) $i['quantity']], $items), JSON_UNESCAPED_UNICODE));
        $ip = Request::ip() ?: '127.0.0.1';
        [$noInstallment, $maxInstallment, $currency] = ['0', '0', 'TL'];

        $hash = $merchant . $ip . $oid . $order['email'] . $amount . $basket . $noInstallment . $maxInstallment . $currency . $test;
        $token = base64_encode(hash_hmac('sha256', $hash . $salt, $key, true));
        $back = Shop::absolute(Router::url('shop.order', ['token' => $order['token']]));

        $ch = curl_init('https://www.paytr.com/odeme/api/get-token');
        curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20, CURLOPT_POSTFIELDS => [
            'merchant_id' => $merchant, 'user_ip' => $ip, 'merchant_oid' => $oid, 'email' => $order['email'], 'payment_amount' => $amount,
            'paytr_token' => $token, 'user_basket' => $basket, 'debug_on' => $test, 'no_installment' => $noInstallment, 'max_installment' => $maxInstallment,
            'user_name' => $order['customer_name'], 'user_address' => trim(implode(' ', [$order['address']['line'] ?? '', $order['address']['district'] ?? '', $order['address']['city'] ?? ''])),
            'user_phone' => $order['phone'], 'merchant_ok_url' => $back . '?paid=1', 'merchant_fail_url' => $back . '?failed=1',
            'timeout_limit' => '30', 'currency' => $currency, 'test_mode' => $test, 'lang' => 'tr',
        ]]);
        $raw = curl_exec($ch);
        if ($raw === false) throw new RuntimeException('PayTR\'ye bağlanılamadı: ' . curl_error($ch));
        $res = json_decode($raw, true) ?: [];
        if (($res['status'] ?? '') !== 'success') {
            Log::warning('PayTR token alınamadı', ['order' => $order['number'], 'reason' => $res['reason'] ?? $raw]);
            throw new RuntimeException('Kartla ödeme başlatılamadı: ' . ($res['reason'] ?? 'bilinmeyen hata'));
        }
        return $res['token'];
    }

    // Bildirim imzası: base64(HMAC-SHA256(merchant_oid + salt + status + total_amount, key))
    public static function validNotification(array $post): bool
    {
        $expected = base64_encode(hash_hmac('sha256', ($post['merchant_oid'] ?? '') . Setting::get('shop_paytr_salt') . ($post['status'] ?? '') . ($post['total_amount'] ?? ''), (string) Setting::get('shop_paytr_key'), true));
        return hash_equals($expected, (string) ($post['hash'] ?? ''));
    }

    public static function orderIdFromOid(string $oid): ?int
    {
        return preg_match('/^S(\d+)T\d+$/', $oid, $m) ? (int) $m[1] : null;
    }
}
