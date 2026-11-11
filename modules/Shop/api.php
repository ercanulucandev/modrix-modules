<?php
// Ödeme kuruluşlarının çağırdığı adresler (/api öneki, CSRF yok; imza/sorgu ile doğrulanır)
use App\Modules\Shop\Controllers\PaymentController;

Router::post('/shop/iyzico/callback/{token}', [PaymentController::class, 'iyzico'])->name('shop.iyzico.callback');
Router::post('/shop/paytr/notify', [PaymentController::class, 'paytr'])->name('shop.paytr.notify');
