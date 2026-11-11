<?php
// Çerez onayı: banner + onay verilene kadar beklemedeki kodlar (type="text/plain" data-consent="kategori")
Hook::on('consent.enabled', fn() => true);

Hook::on('site.body_end', function (array $html) {
    if (Theme::previewing()) return $html;
    $categories = array_filter([
        'analytics'  => Setting::bool('cookies_analytics') ? ['Analitik', 'Ziyaret istatistikleri (Google Analytics vb.).'] : null,
        'functional' => Setting::bool('cookies_functional') ? ['İşlevsel', 'Canlı destek ve benzeri ek özellikler.'] : null,
        'marketing'  => Setting::bool('cookies_marketing') ? ['Pazarlama', 'Reklam ve yeniden hedefleme.'] : null,
    ]);
    return [...$html, (string) View::render('Cookies::banner', ['categories' => $categories])];
});
