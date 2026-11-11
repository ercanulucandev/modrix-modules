<?php
// Blog'un sisteme eklediği bölümler (modül aktifken her istekte yüklenir)
use App\Modules\Blog\Models\Category;
use App\Modules\Blog\Models\Post;

// Admin genel bakış: sayı kutuları
Hook::on('admin.dashboard.stats', fn(array $stats) => [...$stats,
    ['label' => 'Yazı', 'value' => Post::visible()->count(), 'hint' => Post::count(['status' => 'draft']) . ' taslak', 'url' => 'admin/blog', 'order' => 10, 'permission' => 'blog.manage'],
]);
Hook::on('admin.dashboard.actions', fn(array $a) => [...$a, ['label' => 'Yeni yazı', 'url' => 'admin/blog/create', 'icon' => 'plus', 'order' => 20, 'permission' => 'blog.manage']]);

// Admin genel bakış: son yazılar
Hook::on('admin.dashboard.widgets', fn(array $widgets) => [...$widgets,
    ['order' => 10, 'permission' => 'blog.manage', 'html' => (string) View::render('Blog::admin/widget-latest', ['posts' => Post::query()->latest()->limit(5)->get()])],
]);

// Yeni yazı eklenince log
Hook::on('post.created', function (array $post) {
    Log::info('Yeni yazı eklendi', ['id' => $post['id'], 'title' => $post['title']]);
});

// Sayfa önbelleğinden verilen yazı da okunma sayılır
Hook::on('cache.hit', function (string $path) {
    if (preg_match('#^/blog/([a-z0-9-]+)$#', $path, $m) && !in_array($m[1], ['feed.xml'], true)) {
        DB::table('blog_posts')->where('lang', Lang::current())->where('slug', $m[1])->where('status', 'published')->increment('views');
    }
});

// İçerik geçmişi: her kayıtta sürüm (düzenleyici > Geçmiş)
App\Services\Revision::register('post', 'Yazı', 'blog.manage', ['title', 'body', 'excerpt', 'meta_description']);
Hook::on('post.created', fn(array $p) => App\Services\Revision::save('post', $p));
Hook::on('post.updated', fn(array $p) => App\Services\Revision::save('post', $p));
Hook::on('post.deleted', fn(array $p) => App\Services\Revision::forget('post', (int) $p['id']));

// Site içi arama (/search)
Hook::on('site.search', function (array $results, string $q) {
    $like = '%' . addcslashes($q, '%_\\') . '%';
    $posts = Post::visible()->select('title', 'slug', 'excerpt', 'body', 'published_at')
        ->where(fn($w) => $w->whereRaw('title LIKE ?', [$like])->orWhereRaw('excerpt LIKE ?', [$like])->orWhereRaw('body LIKE ?', [$like]))
        ->orderByDesc('published_at')->limit(30)->get();
    foreach ($posts as $p) {
        $results[] = ['title' => $p['title'], 'url' => Router::url('blog.show', ['slug' => $p['slug']]), 'type' => 'Yazı', 'date' => $p['published_at'],
                      'excerpt' => $p['excerpt'] ?: Html::text($p['body'], 200)];
    }
    return $results;
});

// Site haritası (/sitemap.xml): blog sayfası ve yayındaki yazılar
Hook::on('sitemap.urls', function (array $urls) {
    $urls[] = ['loc' => 'blog', 'priority' => '0.7'];
    foreach (array_slice(array_keys(Lang::all()), 1) as $code) $urls[] = ['loc' => Lang::localPath('blog', $code), 'priority' => '0.7'];
    foreach (Post::visible('*')->select('lang', 'slug', 'updated_at')->orderByDesc('published_at')->get() as $p) {
        if (Lang::exists($p['lang'])) $urls[] = ['loc' => Lang::localPath('blog/' . $p['slug'], $p['lang']), 'lastmod' => $p['updated_at'], 'priority' => '0.6'];
    }
    foreach (Category::ordered() as $c) $urls[] = ['loc' => 'blog/kategori/' . $c['slug'], 'priority' => '0.5'];
    return $urls;
});

// Görünüm > Menüler: hazır bağlantılarda blog kategorileri (yazılar listelenmez; çok sayıda olur)
Hook::on('menu.links', fn(array $links) => [...$links,
    ...array_map(fn($c) => ['group' => 'Blog kategorileri', 'label' => $c['name'], 'url' => 'blog/kategori/' . $c['slug']], Category::ordered()),
]);

// <head>: RSS bağlantısı (okuyucular ve tarayıcılar akışı bulsun)
Hook::on('site.head', fn(array $h) => [...$h, '<link rel="alternate" type="application/rss+xml" title="' . View::e(Setting::get('blog_title', 'Blog')) . '" href="' . View::e(Router::url('blog.feed')) . '">']);
