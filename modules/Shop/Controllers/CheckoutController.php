<?php
namespace App\Modules\Shop\Controllers;

use App\Modules\Shop\Services\Cart;
use App\Modules\Shop\Services\Iyzico;
use App\Modules\Shop\Services\Orders;
use App\Modules\Shop\Services\Paytr;
use App\Modules\Shop\Services\Shop;
use Core\Controller;
use Core\HttpException;
use Core\Request;
use Core\Setting;
use Core\Validator;
use Core\View;
use Throwable;

class CheckoutController extends Controller
{
    public function form(): View
    {
        $cart = Cart::contents();
        if (!$cart['lines']) $this->redirect('cart', 'Sepetiniz boş.', 'info');
        $methods = Shop::paymentMethods();
        return $this->view('Shop::checkout', ['title' => 'Ödeme', 'cart' => $cart, 'methods' => $methods, 'codFee' => Shop::cents(Setting::get('shop_cod_fee', 0))]);
    }

    public function store(): void
    {
        $methods = Shop::paymentMethods();
        $data = $this->validate([
            'name'       => 'required|max:100',
            'email'      => 'required|email|max:190',
            'phone'      => 'required|phone',
            'city'       => 'required|max:60',
            'district'   => 'required|max:60',
            'address'    => 'required|max:500',
            'zip'        => 'nullable|max:10',
            'note'       => 'nullable|max:1000',
            'company'    => 'nullable|max:150',
            'tax_office' => 'nullable|max:100',
            'tax_number' => 'nullable|max:20',
            'payment'    => 'required|in:' . implode(',', array_keys($methods) ?: ['none']),
        ], ['name' => 'Ad soyad', 'email' => 'E-posta', 'phone' => 'Telefon', 'city' => 'İl', 'district' => 'İlçe', 'address' => 'Adres', 'payment' => 'Ödeme yöntemi']);
        if (!Request::post('terms')) Validator::fail(['terms' => 'Satış sözleşmesini onaylamanız gerekiyor.']);

        try {
            $order = Orders::create($data, $data['payment']);
        } catch (HttpException $e) {
            $this->redirect('cart', $e->getMessage(), 'error');
        }
        $this->startPayment($order);
    }

    public function order(string $token): View
    {
        $order = Orders::findByToken($token) ?? $this->abort(404, 'Sipariş bulunamadı');
        return $this->view('Shop::order', [
            'title'  => 'Sipariş #' . $order['number'],
            'order'  => $order,
            'items'  => Orders::items($order['id']),
            'bank'   => (string) Setting::get('shop_bank_accounts'),
            'canPay' => in_array($order['payment_method'], ['iyzico', 'paytr'], true) && $order['payment_status'] !== 'paid' && $order['status'] === 'pending',
            'justPaid' => Request::get('paid') !== null,
        ]);
    }

    // Kartla ödemeyi (yeniden) başlat
    public function pay(string $token): void
    {
        $order = Orders::findByToken($token) ?? $this->abort(404);
        if ($order['payment_status'] === 'paid' || $order['status'] !== 'pending') $this->redirect("order/$token");
        $this->startPayment($order);
    }

    // PayTR ödeme çerçevesi
    public function paytrFrame(string $token): View
    {
        $order = Orders::findByToken($token) ?? $this->abort(404);
        if ($order['payment_method'] !== 'paytr' || $order['payment_status'] === 'paid') $this->redirect("order/$token");
        try {
            $frame = Paytr::token($order, Orders::items($order['id']));
        } catch (Throwable $e) {
            $this->redirect("order/$token", $e->getMessage(), 'error');
        }
        return $this->view('Shop::paytr', ['title' => 'Kartla ödeme', 'order' => $order, 'frame' => $frame]);
    }

    private function startPayment(array $order): never
    {
        $back = "order/{$order['token']}";
        try {
            match ($order['payment_method']) {
                'iyzico' => $this->away(Iyzico::start($order, Orders::items($order['id']))),
                'paytr'  => $this->redirect("$back/pay"),
                default  => $this->redirect($back, 'Siparişiniz alındı. Teşekkür ederiz!'),
            };
        } catch (HttpException $e) {
            throw $e;
        } catch (Throwable $e) {
            $this->redirect($back, $e->getMessage() . ' Tekrar deneyebilirsiniz.', 'error');
        }
        exit;
    }

    private function away(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }
}
