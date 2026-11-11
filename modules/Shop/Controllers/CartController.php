<?php
namespace App\Modules\Shop\Controllers;

use App\Modules\Shop\Services\Cart;
use App\Modules\Shop\Services\Shop;
use Core\Controller;
use Core\HttpException;
use Core\Request;
use Core\View;

class CartController extends Controller
{
    public function show(): View
    {
        return $this->view('Shop::cart', ['title' => 'Sepetim', 'cart' => Cart::contents(), 'methods' => Shop::paymentMethods()]);
    }

    public function add(): void
    {
        try {
            Cart::add((int) Request::post('product_id'), ((int) Request::post('variant_id')) ?: null, (int) Request::post('quantity', 1));
        } catch (HttpException $e) {
            $this->back($e->getMessage(), 'error');
        }
        $this->redirect('cart', 'Ürün sepete eklendi.');
    }

    // Adetler: quantity[anahtar] = n (0: sil), ya da remove=anahtar
    public function update(): void
    {
        try {
            if ($remove = (string) Request::post('remove', '')) Cart::update($remove, 0);
            foreach ((array) Request::post('quantity', []) as $key => $qty) Cart::update((string) $key, (int) $qty);
        } catch (HttpException $e) {
            $this->redirect('cart', $e->getMessage(), 'error');
        }
        $this->redirect('cart', $remove ? 'Ürün sepetten çıkarıldı.' : 'Sepet güncellendi.');
    }
}
