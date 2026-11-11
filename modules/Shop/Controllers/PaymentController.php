<?php
namespace App\Modules\Shop\Controllers;

use App\Modules\Shop\Services\Iyzico;
use App\Modules\Shop\Services\Orders;
use App\Modules\Shop\Services\Paytr;
use App\Modules\Shop\Services\Shop;
use Core\Controller;
use Core\Log;
use Core\Request;
use Core\Router;
use Core\Session;
use Throwable;

// Ödeme kuruluşlarından dönüş (api.php: CSRF yok; sonuç kuruluşa sorularak / imzayla doğrulanır)
class PaymentController extends Controller
{
    // iyzico müşteriyi buraya POST eder (token). Sonuç iyzico'ya sorulur.
    public function iyzico(string $token): never
    {
        $order = Orders::findByToken($token) ?? $this->abort(404);
        $back = Router::url('shop.order', ['token' => $token]);
        try {
            $result = Iyzico::verify($order, (string) Request::post('token', ''));
        } catch (Throwable $e) {
            Log::error('iyzico doğrulanamadı', ['order' => $order['number'], 'error' => $e->getMessage()]);
            $result = ['ok' => false, 'message' => 'Ödeme doğrulanamadı.'];
        }
        if ($result['ok']) {
            Orders::markPaid($order, $result['ref']);
            Session::flash('ok', 'Ödemeniz alındı. Teşekkür ederiz!');
        } else {
            Orders::markFailed($order);
            Session::flash('error', 'Ödeme tamamlanamadı' . ($result['message'] ? ': ' . $result['message'] : '.') . ' Tekrar deneyebilirsiniz.');
        }
        header('Location: ' . $back);
        exit;
    }

    // PayTR sunucusu sonucu bildirir; yanıt mutlaka düz "OK" olmalı
    public function paytr(): never
    {
        $post = $_POST;
        if (!Paytr::validNotification($post)) {
            Log::warning('PayTR bildirimi imzası hatalı', ['oid' => $post['merchant_oid'] ?? null, 'ip' => Request::ip()]);
            http_response_code(400);
            exit('PAYTR notification failed: bad hash');
        }
        $order = ($id = Paytr::orderIdFromOid((string) $post['merchant_oid'])) ? Orders::find($id) : null;
        if ($order) {
            if (($post['status'] ?? '') === 'success' && (int) $post['total_amount'] >= Shop::cents($order['total'])) Orders::markPaid($order, (string) $post['merchant_oid']);
            elseif (($post['status'] ?? '') !== 'success') Orders::markFailed($order);
        }
        exit('OK');
    }
}
