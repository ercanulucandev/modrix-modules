<?php
// Modüller > Sayfalar > Ayarlar. Okuma: Setting::get('pages_front_page')
use App\Modules\Pages\Models\Page;

$pages = [];
try {
    foreach (Page::tree('published') as $p) $pages[$p['id']] = str_repeat('— ', $p['depth']) . $p['title'];
} catch (Throwable) {
    // tablo henüz yok (kurulum sırasında)
}

return [
    'description' => 'Ana sayfa seçimi ve sayfaların görünümü.',
    'fields'      => [
        'pages_front_page'    => ['label' => 'Ana sayfa', 'type' => 'select', 'rules' => 'nullable|in:,' . implode(',', array_keys($pages)), 'default' => '',
                                  'options' => ['' => 'Temanın ana sayfası'] + $pages,
                                  'help' => 'WordPress\'teki "Sabit sayfa" gibi: seçilen sayfa sitenin ana sayfası olur. Sadece yayındaki sayfalar listelenir.'],
        'pages_show_children' => ['label' => 'Alt sayfaları listele', 'type' => 'checkbox', 'default' => '1',
                                  'help' => 'Alt sayfası olan bir sayfanın sonunda alt sayfaları kart olarak gösterir.'],
    ],
];
