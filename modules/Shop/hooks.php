<?php
// Mağaza: kategoriler (koleksiyon), sepet simgesi, önbellek dışı adresler, site haritası, arama, menü, genel bakış
use App\Modules\Shop\Services\Shop;
use App\Services\Collection;

Collection::register('shop_categories', [
    'label'       => 'Ürün kategorileri',
    'singular'    => 'Kategori',
    'description' => 'Mağazadaki kategoriler. Ürün düzenlerken seçilir; sitede /shop/category/{adres}.',
    'icon'        => 'grid',
    'permission'  => 'shop.manage',
    'order'       => 26,
    'group'       => 'shop',
    'tab'         => 'Kategoriler',
    'title'       => 'name',
    'image'       => 'image',
    'slug'        => true,
    'url'         => fn(string $slug) => "shop/category/$slug",
    'fields'      => [
        'name'        => ['label' => 'Kategori adı', 'type' => 'text', 'rules' => 'required|max:100'],
        'description' => ['label' => 'Açıklama', 'type' => 'textarea', 'rules' => 'nullable|max:500'],
        'image'       => ['label' => 'Görsel', 'type' => 'image'],
    ],
]);

// Admin menüsü: siparişler (bekleyen sayısıyla)
Hook::on('admin.menu', fn(array $menu) => [...$menu,
    ['url' => 'admin/shop/orders', 'label' => 'Siparişler', 'icon' => 'cart', 'order' => 24, 'group' => 'shop', 'permission' => 'shop.manage',
     'badge' => DB::table('shop_orders')->whereIn('status', ['pending', 'processing'])->count() ?: null],
]);

// Kişiye özel sayfalar önbelleğe girmez
Hook::on('cache.skip', fn(array $paths) => [...$paths, '/cart', '/checkout', '/order/']);

// Üst kısımda sepet simgesi; sayı 'modrix_cart' çerezinden (önbellekli sayfada da doğru)
Hook::on('site.header_actions', fn(array $html) => [...$html, (string) View::render('Shop::cart-icon')]);

Hook::on('menu.links', fn(array $links) => [...$links,
    ['group' => 'Mağaza', 'label' => Setting::get('shop_title', 'Mağaza'), 'url' => 'shop'],
    ['group' => 'Mağaza', 'label' => 'Sepet', 'url' => 'cart'],
    ...array_map(fn($c) => ['group' => 'Mağaza', 'label' => $c['name'], 'url' => 'shop/category/' . $c['slug']], Collection::items('shop_categories')),
]);

Hook::on('sitemap.urls', function (array $urls) {
    $urls[] = ['loc' => 'shop', 'priority' => '0.7'];
    foreach (Collection::items('shop_categories') as $c) $urls[] = ['loc' => 'shop/category/' . $c['slug'], 'priority' => '0.6'];
    foreach (Shop::products()->select('slug', 'updated_at')->get() as $p) $urls[] = ['loc' => 'shop/' . $p['slug'], 'lastmod' => $p['updated_at'], 'priority' => '0.7'];
    return $urls;
});

Hook::on('site.search', function (array $results, string $q) {
    $like = '%' . addcslashes($q, '%_\\') . '%';
    $rows = Shop::products()->select('name', 'slug', 'summary', 'price')
        ->where(fn($w) => $w->whereRaw('name LIKE ?', [$like])->orWhereRaw('summary LIKE ?', [$like])->orWhereRaw('sku LIKE ?', [$like]))->limit(30)->get();
    foreach ($rows as $p) {
        $results[] = ['title' => $p['name'], 'url' => Router::url('shop.product', ['slug' => $p['slug']]), 'type' => 'Ürün · ' . Shop::money($p['price']), 'excerpt' => (string) $p['summary']];
    }
    return $results;
});

Hook::on('admin.dashboard.stats', function (array $stats) {
    $month = DB::value("SELECT COALESCE(SUM(total), 0) FROM shop_orders WHERE payment_status = 'paid' AND paid_at >= ?", [date('Y-m-01 00:00:00')]);
    return [...$stats,
        ['label' => 'Bekleyen sipariş', 'value' => DB::table('shop_orders')->whereIn('status', ['pending', 'processing'])->count(), 'url' => 'admin/shop/orders', 'order' => 1, 'permission' => 'shop.manage'],
        ['label' => 'Bu ay satış', 'value' => Shop::money($month), 'url' => 'admin/shop/orders', 'order' => 2, 'permission' => 'shop.manage'],
    ];
});

// Genel bakış: hızlı işlem, bekleyen işler, satış grafiği, son siparişler
Hook::on('admin.dashboard.actions', fn(array $a) => [...$a, ['label' => 'Ürün ekle', 'url' => 'admin/shop/products/create', 'icon' => 'plus', 'order' => 30, 'permission' => 'shop.manage']]);
Hook::on('admin.dashboard.todos', function (array $t) {
    $ship = DB::table('shop_orders')->where('status', 'processing')->count();
    $bank = DB::table('shop_orders')->where('status', 'pending')->where('payment_method', 'bank')->count();
    if ($ship) $t[] = ['text' => "$ship sipariş hazırlanmayı / kargoya verilmeyi bekliyor.", 'url' => 'admin/shop/orders?status=processing', 'tone' => 'warning', 'order' => 10, 'permission' => 'shop.manage'];
    if ($bank) $t[] = ['text' => "$bank siparişin havale ödemesi bekleniyor (gelince \"Ödeme alındı\" deyin).", 'url' => 'admin/shop/orders?status=pending', 'tone' => 'info', 'order' => 11, 'permission' => 'shop.manage'];
    return $t;
});
Hook::on('admin.dashboard.series', fn(array $s) => [...$s,
    ['label' => 'Satış', 'format' => 'money', 'order' => 10, 'permission' => 'shop.manage',
     'points' => App\Services\Dashboard::daily('shop_orders', 'paid_at', 'SUM(total)', "payment_status = 'paid'"),
     'previous' => App\Services\Dashboard::previousTotal('shop_orders', 'paid_at', 'SUM(total)', "payment_status = 'paid'")],
    ['label' => 'Sipariş', 'order' => 11, 'permission' => 'shop.manage',
     'points' => App\Services\Dashboard::daily('shop_orders', 'created_at', 'COUNT(*)', "status != 'cancelled'"),
     'previous' => App\Services\Dashboard::previousTotal('shop_orders', 'created_at', 'COUNT(*)', "status != 'cancelled'")],
]);
Hook::on('admin.dashboard.widgets', fn(array $w) => [...$w, ['order' => 5, 'permission' => 'shop.manage',
    'html' => (string) View::render('Shop::admin/widget', ['orders' => DB::table('shop_orders')->orderByDesc('id')->limit(6)->get()])]]);
