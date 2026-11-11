<?php
// Blog modülü
//   Site:      /blog, /blog/kategori/{slug}, /blog/etiket/{slug}, /blog/feed.xml, /blog/{slug}
//   Admin:     /admin/blog (liste), /admin/blog/create, /admin/blog/{id}/edit (düzenleyici), /admin/blog/categories
//   Admin API: /admin/api/blog/...  (JS buradan çalışır)
use App\Modules\Blog\Controllers\Admin\PostApiController;
use App\Modules\Blog\Controllers\Admin\PostController as AdminPostController;
use App\Modules\Blog\Controllers\Admin\TaxonomyController;
use App\Modules\Blog\Controllers\PostController;
use App\Modules\Blog\Middlewares\NoCacheMiddleware;

// ---- Site
Router::group(['prefix' => '/blog'], function () {
    Router::get('/', [PostController::class, 'index'])->name('blog.index');
    Router::get('/feed.xml', [PostController::class, 'feed'])->name('blog.feed');
    Router::get('/kategori/{slug}', [PostController::class, 'category'])->name('blog.category');
    Router::get('/etiket/{slug}', [PostController::class, 'tag'])->name('blog.tag');
    Router::get('/{slug}', [PostController::class, 'show'])->name('blog.show');
});

// ---- Admin sayfaları
Router::group(['prefix' => '/admin/blog', 'middleware' => ['admin', 'can:blog.manage']], function () {
    Router::get('/', [AdminPostController::class, 'index'])->name('admin.blog.index');
    Router::get('/create', [AdminPostController::class, 'create'])->name('admin.blog.create');
    Router::get('/{id}/edit', [AdminPostController::class, 'edit'])->name('admin.blog.edit');
    Router::post('/{id}/translate/{lang}', [AdminPostController::class, 'translate'])->name('admin.blog.translate');
    Router::get('/categories', [TaxonomyController::class, 'index'])->name('admin.blog.categories');
    Router::post('/categories', [TaxonomyController::class, 'store'])->name('admin.blog.categories.store');
    Router::post('/categories/{id}', [TaxonomyController::class, 'update'])->name('admin.blog.categories.update');
    Router::post('/categories/{id}/delete', [TaxonomyController::class, 'destroy'])->name('admin.blog.categories.delete');
    Router::post('/tags/{id}/delete', [TaxonomyController::class, 'destroyTag'])->name('admin.blog.tags.delete');
});

// ---- Admin API (oturum + CSRF başlığı; yanıtlar önbelleğe alınmaz)
Router::group(['prefix' => '/admin/api/blog', 'middleware' => ['admin', 'can:blog.manage', NoCacheMiddleware::class]], function () {
    Router::get('/options', [PostApiController::class, 'options'])->name('admin.api.blog.options');
    Router::post('/categories', [PostApiController::class, 'storeCategory'])->name('admin.api.blog.categories.store');
    Router::get('/posts', [PostApiController::class, 'index'])->name('admin.api.blog.index');
    Router::post('/posts', [PostApiController::class, 'store'])->name('admin.api.blog.store');
    Router::get('/posts/{id}', [PostApiController::class, 'show'])->name('admin.api.blog.show');
    Router::put('/posts/{id}', [PostApiController::class, 'update'])->name('admin.api.blog.update');
    Router::post('/posts/{id}/duplicate', [PostApiController::class, 'duplicate'])->name('admin.api.blog.duplicate');
    Router::delete('/posts/{id}', [PostApiController::class, 'destroy'])->name('admin.api.blog.destroy');
});
