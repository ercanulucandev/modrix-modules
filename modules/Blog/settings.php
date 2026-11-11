<?php
// Admin > Modüller > Blog > Ayarlar. Anahtarlar 'blog_' ile başlar; okuma: Setting::get('blog_per_page')
return [
    'description' => 'Sitedeki blog sayfalarının görünümü. Ana sayfadaki son yazılar: Görünüm > Özelleştir > Ana sayfa bölümleri > "Son yazılar".',
    'fields'      => [
        'blog_title'       => ['label' => 'Sayfa başlığı', 'type' => 'text', 'rules' => 'nullable|max:80', 'default' => 'Blog',
                               'help' => 'Blog sayfasının başlığı ve tarayıcı sekmesindeki adı.'],
        'blog_description' => ['label' => 'Sayfa açıklaması', 'type' => 'textarea', 'rules' => 'nullable|max:300',
                               'help' => 'Başlığın altında görünür; arama motorları için açıklama olarak da kullanılır.'],
        'blog_per_page'    => ['label' => 'Sayfa başına yazı', 'type' => 'number', 'rules' => 'required|integer|between:1,50', 'default' => '10'],
        'blog_show_views'  => ['label' => 'Okunma sayısını göster', 'type' => 'checkbox', 'default' => '1',
                               'help' => 'Liste ve yazı sayfasında "120 okunma" bilgisi.'],
    ],
];
