<?php
// SSS modülü: sorular Admin > SSS'de (koleksiyon), sitede /faq, ana sayfada bölüm, içerikte [faq] / [faq grup-adi]
use App\Services\Collection;

Collection::register('faq', [
    'group'       => 'showcase',
    'label'       => 'SSS',
    'singular'    => 'Soru',
    'description' => 'Sitede /faq sayfasında, ana sayfada "Sıkça sorulanlar" bölümüyle ve sayfalarda [faq] yazarak görünür.',
    'icon'        => 'help',
    'permission'  => 'faq.manage',
    'order'       => 40,
    'title'       => 'question',
    'subtitle'    => 'group',
    'fields'      => [
        'question' => ['label' => 'Soru', 'type' => 'text', 'rules' => 'required|max:255'],
        'answer'   => ['label' => 'Cevap', 'type' => 'textarea', 'rules' => 'required|max:5000'],
        'group'    => ['label' => 'Grup', 'type' => 'text', 'rules' => 'nullable|max:60', 'help' => 'İsteğe bağlı. Aynı grubu yazdığınız sorular birlikte listelenir (ör. Ödeme, Kargo). Sayfada yalnız bir grup: [faq odeme]'],
    ],
]);

// [faq] tüm sorular, [faq odeme] sadece "Ödeme" grubu
Hook::on('content.html', function (string $html) {
    if (!str_contains($html, '[faq')) return $html;
    return preg_replace_callback('#(?:<p>\s*)?\[faq(?:\s+([a-z0-9-]{1,60}))?\](?:\s*</p>)?#i', function ($m) {
        $items = App\Modules\Faq\Controllers\FaqController::items($m[1] ?? null);
        return $items ? (string) View::render('Faq::list', ['items' => $items]) : '';
    }, $html);
});

Hook::on('menu.links', fn(array $links) => [...$links, ['group' => 'Modül sayfaları', 'label' => Setting::get('faq_title', 'SSS'), 'url' => 'faq']]);
Hook::on('sitemap.urls', fn(array $urls) => [...$urls, ['loc' => 'faq', 'priority' => '0.5']]);
Hook::on('site.search', function (array $results, string $q) {
    foreach (Collection::items('faq', fn($i) => mb_stripos($i['question'] . ' ' . $i['answer'], $q) !== false) as $i) {
        $results[] = ['title' => $i['question'], 'url' => Router::url('faq.index') . '#soru-' . $i['id'], 'type' => 'SSS', 'excerpt' => mb_strimwidth($i['answer'], 0, 200, '…')];
    }
    return $results;
});
