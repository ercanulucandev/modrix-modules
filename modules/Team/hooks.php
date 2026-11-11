<?php
// Ekip modülü: üyeler Admin > Ekip'te (koleksiyon), sitede /team, ana sayfada bölüm, içerikte [team]
use App\Services\Collection;

Collection::register('team', [
    'group'       => 'showcase',
    'label'       => 'Ekip',
    'singular'    => 'Üye',
    'description' => 'Sitede /team sayfasında, ana sayfada "Ekibimiz" bölümüyle ve sayfalarda [team] yazarak görünür.',
    'icon'        => 'users',
    'permission'  => 'team.manage',
    'order'       => 41,
    'title'       => 'name',
    'subtitle'    => 'role',
    'image'       => 'photo',
    'fields'      => [
        'name'      => ['label' => 'Ad soyad', 'type' => 'text', 'rules' => 'required|max:100'],
        'role'      => ['label' => 'Görevi', 'type' => 'text', 'rules' => 'nullable|max:100', 'placeholder' => 'Kurucu, Tasarımcı...'],
        'photo'     => ['label' => 'Fotoğraf', 'type' => 'image', 'help' => 'Kare ya da dikey fotoğraf önerilir.'],
        'bio'       => ['label' => 'Kısa tanıtım', 'type' => 'textarea', 'rules' => 'nullable|max:600'],
        'email'     => ['label' => 'E-posta', 'type' => 'email', 'rules' => 'nullable|email|max:190'],
        'linkedin'  => ['label' => 'LinkedIn', 'type' => 'url', 'rules' => 'nullable|url|max:255'],
        'instagram' => ['label' => 'Instagram', 'type' => 'url', 'rules' => 'nullable|url|max:255'],
        'x'         => ['label' => 'X (Twitter)', 'type' => 'url', 'rules' => 'nullable|url|max:255'],
    ],
]);

Hook::on('content.html', function (string $html) {
    if (!str_contains($html, '[team]')) return $html;
    $items = Collection::items('team');
    return preg_replace('#(?:<p>\s*)?\[team\](?:\s*</p>)?#i', $items ? (string) View::render('Team::grid', ['items' => $items, 'columns' => 3]) : '', $html);
});
Hook::on('menu.links', fn(array $links) => [...$links, ['group' => 'Modül sayfaları', 'label' => Setting::get('team_title', 'Ekibimiz'), 'url' => 'team']]);
Hook::on('sitemap.urls', fn(array $urls) => [...$urls, ['loc' => 'team', 'priority' => '0.5']]);
