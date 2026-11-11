<?php
// Portfolyo: projeler Admin > Portfolyo'da (adresli koleksiyon), /portfolio, /portfolio/{adres}, bölüm, [gallery adres]
use App\Services\Collection;

Collection::register('portfolio', [
    'group'       => 'showcase',
    'label'       => 'Portfolyo',
    'singular'    => 'Proje',
    'description' => 'Sitede /portfolio sayfasında listelenir, her projenin kendi sayfası vardır. Bir projenin görselleri sayfalara [gallery proje-adresi] ile eklenir.',
    'icon'        => 'image',
    'permission'  => 'portfolio.manage',
    'order'       => 44,
    'title'       => 'title',
    'subtitle'    => 'category',
    'image'       => 'cover',
    'slug'        => true,
    'url'         => fn(string $slug) => "portfolio/$slug",
    'fields'      => [
        'title'       => ['label' => 'Proje adı', 'type' => 'text', 'rules' => 'required|max:150'],
        'category'    => ['label' => 'Kategori', 'type' => 'text', 'rules' => 'nullable|max:60', 'help' => 'Listede süzme düğmesi olur (ör. Web, Kurumsal kimlik).'],
        'cover'       => ['label' => 'Kapak görseli', 'type' => 'image', 'help' => 'Boşsa galerinin ilk görseli.'],
        'images'      => ['label' => 'Galeri', 'type' => 'gallery', 'max' => 60, 'help' => 'Proje sayfasında ızgara; tıklayınca büyür.'],
        'summary'     => ['label' => 'Kısa açıklama', 'type' => 'textarea', 'rules' => 'nullable|max:300', 'help' => 'Listede ve arama motorlarında görünür.'],
        'description' => ['label' => 'Proje hakkında', 'type' => 'textarea', 'rules' => 'nullable|max:10000'],
        'client'      => ['label' => 'Müşteri', 'type' => 'text', 'rules' => 'nullable|max:100'],
        'year'        => ['label' => 'Yıl', 'type' => 'text', 'rules' => 'nullable|max:20'],
        'link'        => ['label' => 'Bağlantı', 'type' => 'url', 'rules' => 'nullable|url|max:255', 'help' => 'Canlı site vb.'],
    ],
]);

// [gallery proje-adresi]: projenin görselleri ızgara olarak
Hook::on('content.html', function (string $html) {
    if (!str_contains($html, '[gallery ')) return $html;
    return preg_replace_callback('#(?:<p>\s*)?\[gallery\s+([a-z0-9-]{1,150})\](?:\s*</p>)?#i', function ($m) {
        $p = Collection::findBySlug('portfolio', strtolower($m[1]));
        return $p && $p['images'] ? (string) View::render('Portfolio::gallery', ['images' => $p['images'], 'title' => $p['title']]) : '';
    }, $html);
});
Hook::on('menu.links', fn(array $links) => [...$links, ['group' => 'Modül sayfaları', 'label' => Setting::get('portfolio_title', 'Portfolyo'), 'url' => 'portfolio']]);
Hook::on('sitemap.urls', function (array $urls) {
    $urls[] = ['loc' => 'portfolio', 'priority' => '0.6'];
    foreach (Collection::items('portfolio') as $p) $urls[] = ['loc' => 'portfolio/' . $p['slug'], 'lastmod' => $p['updated_at'], 'priority' => '0.5'];
    return $urls;
});
Hook::on('site.search', function (array $results, string $q) {
    foreach (Collection::items('portfolio', fn($p) => mb_stripos($p['title'] . ' ' . $p['summary'] . ' ' . $p['description'] . ' ' . $p['client'], $q) !== false) as $p) {
        $results[] = ['title' => $p['title'], 'url' => Router::url('portfolio.show', ['slug' => $p['slug']]), 'type' => 'Proje', 'excerpt' => $p['summary'] ?: mb_strimwidth($p['description'], 0, 200, '…')];
    }
    return $results;
});
