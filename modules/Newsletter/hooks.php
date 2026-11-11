<?php
// Bülten: [newsletter] kısa kodu, genel bakış sayısı
use App\Modules\Newsletter\Services\Newsletter;

Hook::on('content.html', function (string $html) {
    if (!str_contains($html, '[newsletter]')) return $html;
    return preg_replace('#(?:<p>\s*)?\[newsletter\](?:\s*</p>)?#i', (string) View::render('Newsletter::form', ['button' => 'Abone ol']), $html);
});
Hook::on('admin.dashboard.series', fn(array $s) => [...$s, ['label' => 'Yeni abone', 'order' => 40, 'permission' => 'newsletter.manage',
    'points' => App\Services\Dashboard::daily('newsletter_subscribers', 'created_at', 'COUNT(*)', "status = 'active'"),
    'previous' => App\Services\Dashboard::previousTotal('newsletter_subscribers', 'created_at', 'COUNT(*)', "status = 'active'")]]);
Hook::on('admin.dashboard.stats', fn(array $stats) => [...$stats,
    ['label' => 'Bülten abonesi', 'value' => Newsletter::counts()['active'], 'url' => 'admin/newsletter', 'order' => 35, 'permission' => 'newsletter.manage'],
]);
