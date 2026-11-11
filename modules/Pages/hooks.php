<?php
// Sayfalar modülünün sisteme eklediği bağlantılar (modül aktifken her istekte yüklenir)
use App\Modules\Pages\Controllers\PageController;
use App\Modules\Pages\Models\Page;

// Ana sayfa: Modüller > Sayfalar > Ayarlar'da bir sayfa seçildiyse temanın ana sayfası yerine o gösterilir
// Çoklu dilde seçilen sayfanın o dildeki çevirisi kullanılır
Hook::on('site.front_page', function ($view) {
    if ($view) return $view;
    $page = PageController::frontPage();
    return $page ? (new PageController)->render($page, true) : $view;
});

// Görünüm > Menüler: "Hazır bağlantılar" içinde yayındaki sayfalar (alt sayfalar girintili)
Hook::on('menu.links', fn(array $links) => [...$links,
    ...array_map(fn($p) => ['group' => 'Sayfalar', 'label' => str_repeat('— ', $p['depth']) . $p['title'], 'url' => $p['path']], Page::tree('published')),
]);

// İçerik geçmişi: her kayıtta sürüm (düzenleyici > Geçmiş)
App\Services\Revision::register('page', 'Sayfa', 'pages.manage', ['title', 'body', 'meta_description']);
Hook::on('page.created', fn(array $p) => App\Services\Revision::save('page', $p));
Hook::on('page.updated', fn(array $p) => App\Services\Revision::save('page', $p));
Hook::on('page.deleted', fn(array $p) => App\Services\Revision::forget('page', (int) $p['id']));

// Site içi arama (/search)
Hook::on('site.search', function (array $results, string $q) {
    $like = '%' . addcslashes($q, '%_\\') . '%';
    $pages = Page::query()->select('title', 'path', 'body', 'updated_at')->where('status', 'published')->where('lang', Lang::current())
        ->where(fn($w) => $w->whereRaw('title LIKE ?', [$like])->orWhereRaw('body LIKE ?', [$like]))
        ->limit(30)->get();
    foreach ($pages as $p) {
        $results[] = ['title' => $p['title'], 'url' => Request::url($p['path']), 'type' => 'Sayfa', 'date' => null, 'excerpt' => Html::text($p['body'], 200)];
    }
    return $results;
});

// Genel bakış: hızlı işlem
Hook::on('admin.dashboard.actions', fn(array $a) => [...$a, ['label' => 'Yeni sayfa', 'url' => 'admin/pages/create', 'icon' => 'plus', 'order' => 10, 'permission' => 'pages.manage']]);

// Genel bakış: sayı kutusu
Hook::on('admin.dashboard.stats', fn(array $stats) => [...$stats,
    ['label' => 'Sayfa', 'value' => Page::count(['status' => 'published']), 'url' => 'admin/pages', 'order' => 5, 'permission' => 'pages.manage'],
]);

// Site haritası (/sitemap.xml): yayındaki sayfalar (ana sayfa olarak seçilen sayfa '/' adresindedir)
Hook::on('sitemap.urls', function (array $urls) {
    $front = (int) Setting::get('pages_front_page');
    foreach (Page::query()->select('id', 'lang', 'path', 'updated_at')->where('status', 'published')->get() as $p) {
        if ((int) $p['id'] !== $front && Lang::exists($p['lang'])) $urls[] = ['loc' => Lang::localPath($p['path'], $p['lang']), 'lastmod' => $p['updated_at'], 'priority' => '0.8'];
    }
    return $urls;
});
