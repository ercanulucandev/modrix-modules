<?php
// Referanslar modülü: müşteri yorumları ve logolar (iki koleksiyon), /references, bölümler, [testimonials] [clients]
use App\Services\Collection;

Collection::register('testimonials', [
    'group'       => 'showcase',
    'tab'         => 'Yorumlar',
    'label'       => 'Müşteri yorumları',
    'singular'    => 'Yorum',
    'description' => 'Sitede /references sayfasında, ana sayfada "Müşteri yorumları" bölümüyle ve sayfalarda [testimonials] yazarak görünür.',
    'icon'        => 'quote',
    'permission'  => 'references.manage',
    'order'       => 42,
    'title'       => 'name',
    'subtitle'    => 'company',
    'image'       => 'photo',
    'fields'      => [
        'name'    => ['label' => 'Ad soyad', 'type' => 'text', 'rules' => 'required|max:100'],
        'company' => ['label' => 'Şirket / görev', 'type' => 'text', 'rules' => 'nullable|max:100'],
        'quote'   => ['label' => 'Yorum', 'type' => 'textarea', 'rules' => 'required|max:1000'],
        'rating'  => ['label' => 'Puan', 'type' => 'select', 'default' => '5', 'options' => ['5' => '★★★★★', '4' => '★★★★', '3' => '★★★', '0' => 'Gösterme']],
        'photo'   => ['label' => 'Fotoğraf', 'type' => 'image'],
    ],
]);
Collection::register('clients', [
    'group'       => 'showcase',
    'tab'         => 'Logolar',
    'label'       => 'Referans logoları',
    'singular'    => 'Logo',
    'description' => 'Çalıştığınız markaların logoları. /references sayfasında, "Referanslar" bölümünde ve [clients] ile görünür.',
    'icon'        => 'award',
    'permission'  => 'references.manage',
    'order'       => 43,
    'title'       => 'name',
    'image'       => 'logo',
    'fields'      => [
        'name' => ['label' => 'Marka adı', 'type' => 'text', 'rules' => 'required|max:100'],
        'logo' => ['label' => 'Logo', 'type' => 'image', 'rules' => 'required', 'help' => 'Şeffaf zeminli PNG/SVG en iyisi.'],
        'url'  => ['label' => 'Web sitesi', 'type' => 'url', 'rules' => 'nullable|url|max:255', 'help' => 'İsteğe bağlı; logo bağlantı olur.'],
    ],
]);

Hook::on('content.html', function (string $html) {
    foreach (['testimonials' => 'References::testimonials', 'clients' => 'References::logos'] as $code => $view) {
        if (!str_contains($html, "[$code]")) continue;
        $items = Collection::items($code);
        $html = preg_replace("#(?:<p>\s*)?\[$code\](?:\s*</p>)?#i", $items ? (string) View::render($view, ['items' => $items]) : '', $html);
    }
    return $html;
});
Hook::on('menu.links', fn(array $links) => [...$links, ['group' => 'Modül sayfaları', 'label' => Setting::get('references_title', 'Referanslar'), 'url' => 'references']]);
Hook::on('sitemap.urls', fn(array $urls) => [...$urls, ['loc' => 'references', 'priority' => '0.5']]);
