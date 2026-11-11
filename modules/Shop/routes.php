<?php
// Mağaza
//   Site:  /shop, /shop/category/{adres}, /shop/{adres}, /cart, /checkout, /order/{token}
//   Admin: /admin/shop/products, /admin/shop/orders
//   Ödeme bildirimleri: api.php (CSRF dışı)
use App\Modules\Shop\Controllers\Admin\OrderController as AdminOrderController;
use App\Modules\Shop\Controllers\Admin\ProductController;
use App\Modules\Shop\Controllers\CartController;
use App\Modules\Shop\Controllers\CheckoutController;
use App\Modules\Shop\Controllers\ShopController;

Router::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Router::get('/shop/category/{slug}', [ShopController::class, 'category'])->name('shop.category');
Router::get('/shop/{slug}', [ShopController::class, 'show'])->name('shop.product');

Router::get('/cart', [CartController::class, 'show'])->name('shop.cart');
Router::post('/cart/add', [CartController::class, 'add'])->name('shop.cart.add');
Router::post('/cart/update', [CartController::class, 'update'])->name('shop.cart.update');

Router::get('/checkout', [CheckoutController::class, 'form'])->name('shop.checkout');
Router::post('/checkout', [CheckoutController::class, 'store'])->name('shop.checkout.store');
Router::get('/order/{token}', [CheckoutController::class, 'order'])->name('shop.order');
Router::post('/order/{token}/pay', [CheckoutController::class, 'pay'])->name('shop.order.pay');
Router::get('/order/{token}/pay', [CheckoutController::class, 'paytrFrame'])->name('shop.order.paytr');

Router::group(['prefix' => '/admin/shop', 'middleware' => ['admin', 'can:shop.manage']], function () {
    Router::get('/products', [ProductController::class, 'index'])->name('admin.shop.products');
    Router::get('/products/create', [ProductController::class, 'create'])->name('admin.shop.products.create');
    Router::post('/products', [ProductController::class, 'store'])->name('admin.shop.products.store');
    Router::get('/products/{id}/edit', [ProductController::class, 'edit'])->name('admin.shop.products.edit');
    Router::post('/products/{id}', [ProductController::class, 'update'])->name('admin.shop.products.update');
    Router::post('/products/{id}/delete', [ProductController::class, 'destroy'])->name('admin.shop.products.delete');
    Router::get('/orders', [AdminOrderController::class, 'index'])->name('admin.shop.orders');
    Router::get('/orders/{id}', [AdminOrderController::class, 'show'])->name('admin.shop.orders.show');
    Router::post('/orders/{id}', [AdminOrderController::class, 'update'])->name('admin.shop.orders.update');
    Router::post('/orders/{id}/paid', [AdminOrderController::class, 'paid'])->name('admin.shop.orders.paid');
});
