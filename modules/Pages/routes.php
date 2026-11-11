<?php
// Sayfalar modülü
//   Site:      /{adres}   (Router::fallback: hiçbir route eşleşmezse sayfalarda aranır; tanımlı route'ları ezmez)
//   Admin:     /admin/pages, /admin/pages/create, /admin/pages/{id}/edit   (sayfa iskeletleri)
//   Admin API: /admin/api/pages/...                                        (JS buradan çalışır)
use App\Modules\Pages\Controllers\Admin\PageApiController;
use App\Modules\Pages\Controllers\Admin\PageController as AdminPageController;
use App\Modules\Pages\Controllers\PageController;

// ---- Site
Router::fallback(fn(string $path) => PageController::resolve($path));

// ---- Admin sayfaları
Router::group(['prefix' => '/admin/pages', 'middleware' => ['admin', 'can:pages.manage']], function () {
    Router::get('/', [AdminPageController::class, 'index'])->name('admin.pages.index');
    Router::get('/create', [AdminPageController::class, 'create'])->name('admin.pages.create');
    Router::get('/{id}/edit', [AdminPageController::class, 'edit'])->name('admin.pages.edit');
    Router::post('/{id}/translate/{lang}', [AdminPageController::class, 'translate'])->name('admin.pages.translate');
});

// ---- Admin API
Router::group(['prefix' => '/admin/api/pages', 'middleware' => ['admin', 'can:pages.manage']], function () {
    Router::get('/', [PageApiController::class, 'index'])->name('admin.api.pages.index');
    Router::get('/options', [PageApiController::class, 'options'])->name('admin.api.pages.options');
    Router::post('/', [PageApiController::class, 'store'])->name('admin.api.pages.store');
    Router::get('/{id}', [PageApiController::class, 'show'])->name('admin.api.pages.show');
    Router::put('/{id}', [PageApiController::class, 'update'])->name('admin.api.pages.update');
    Router::post('/{id}/duplicate', [PageApiController::class, 'duplicate'])->name('admin.api.pages.duplicate');
    Router::delete('/{id}', [PageApiController::class, 'destroy'])->name('admin.api.pages.destroy');
});
