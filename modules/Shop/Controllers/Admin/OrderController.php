<?php
namespace App\Modules\Shop\Controllers\Admin;

use App\Controllers\Admin\AdminController;
use App\Modules\Shop\Services\Orders;
use App\Modules\Shop\Services\Shop;
use App\Services\Activity;
use Core\Database;
use Core\Request;
use Core\View;

// Admin > Siparişler
class OrderController extends AdminController
{
    public function index(): View
    {
        $status = (string) Request::get('status', '');
        $q = trim((string) Request::get('q', ''));
        $rows = Database::table('shop_orders')
            ->when(isset(Shop::STATUSES[$status]), fn($query) => $query->where('status', $status))
            ->search(['number', 'customer_name', 'email', 'phone'], $q)->orderByDesc('id')->paginate(30);
        $counts = array_column(Database::all('SELECT status, COUNT(*) n FROM shop_orders GROUP BY status'), 'n', 'status');
        return $this->view('Shop::admin/orders', ['title' => 'Siparişler', 'rows' => $rows, 'counts' => $counts, 'filter' => ['status' => $status, 'q' => $q]]);
    }

    public function show(string $id): View
    {
        $order = Orders::find((int) $id) ?? $this->abort(404);
        return $this->view('Shop::admin/order', ['title' => 'Sipariş #' . $order['number'], 'order' => $order, 'items' => Orders::items($order['id'])]);
    }

    public function update(string $id): void
    {
        $order = Orders::find((int) $id) ?? $this->abort(404);
        $data = $this->validate(['status' => 'required|in:' . implode(',', array_keys(Shop::STATUSES)), 'cargo_company' => 'nullable|max:60',
                                 'tracking_number' => 'nullable|max:60', 'admin_note' => 'nullable|max:1000'], ['status' => 'Durum']);
        Orders::setStatus($order, $data['status'], [
            'cargo_company' => $data['cargo_company'] ?: null, 'tracking_number' => $data['tracking_number'] ?: null, 'admin_note' => $data['admin_note'] ?: null,
        ], (bool) Request::post('notify'));
        Activity::log('shop.order', "#{$order['number']} siparişini güncelledi: " . Shop::STATUSES[$data['status']], "admin/shop/orders/{$order['id']}");
        $this->redirect("admin/shop/orders/{$order['id']}", 'Sipariş güncellendi.' . (Request::post('notify') ? ' Müşteriye e-posta gönderildi.' : ''));
    }

    public function paid(string $id): void
    {
        $order = Orders::find((int) $id) ?? $this->abort(404);
        Orders::markPaid($order, Request::post('ref') ?: null);
        Activity::log('shop.order', "#{$order['number']} siparişinin ödemesini onayladı", "admin/shop/orders/{$order['id']}");
        $this->redirect("admin/shop/orders/{$order['id']}", 'Ödeme alındı olarak işaretlendi; müşteriye e-posta gönderildi.');
    }
}
