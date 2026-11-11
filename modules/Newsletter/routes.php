<?php
// Bülten: abone ol, onayla, ayrıl; Admin > Bülten
use App\Modules\Newsletter\Controllers\Admin\SubscriberController;
use App\Modules\Newsletter\Controllers\SubscribeController;

Router::post('/newsletter/subscribe', [SubscribeController::class, 'store'])->name('newsletter.subscribe');
Router::get('/newsletter/confirm/{token}', [SubscribeController::class, 'confirm'])->name('newsletter.confirm');
Router::get('/newsletter/unsubscribe/{token}', [SubscribeController::class, 'unsubscribeForm'])->name('newsletter.unsubscribe');
Router::post('/newsletter/unsubscribe/{token}', [SubscribeController::class, 'unsubscribe']);

Router::group(['prefix' => '/admin/newsletter', 'middleware' => ['admin', 'can:newsletter.manage']], function () {
    Router::get('/', [SubscriberController::class, 'index'])->name('admin.newsletter');
    Router::get('/export', [SubscriberController::class, 'export'])->name('admin.newsletter.export');
    Router::get('/send', [SubscriberController::class, 'compose'])->name('admin.newsletter.compose');
    Router::post('/send', [SubscriberController::class, 'send'])->name('admin.newsletter.send');
    Router::post('/{id}/delete', [SubscriberController::class, 'destroy'])->name('admin.newsletter.delete');
});
