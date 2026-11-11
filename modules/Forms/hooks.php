<?php
// Formlar modülünün sisteme eklediği bağlantılar
use App\Modules\Forms\Models\Entry;
use App\Modules\Forms\Models\Form;

// Admin menüsü: okunmamış mesaj sayısıyla
Hook::on('admin.menu', fn(array $menu) => [...$menu,
    ['url' => 'admin/forms', 'label' => 'Formlar', 'icon' => 'mail', 'order' => 30, 'group' => 'content', 'permission' => 'forms.manage', 'badge' => Entry::unreadCount() ?: null],
]);

// Genel bakış: okunmamış mesajlar, 30 günlük grafik
Hook::on('admin.dashboard.todos', fn(array $t) => ($n = Entry::unreadCount()) ? [...$t, ['text' => "$n okunmamış form mesajı var.", 'url' => 'admin/forms', 'tone' => 'warning', 'order' => 20, 'permission' => 'forms.manage']] : $t);
Hook::on('admin.dashboard.series', fn(array $s) => [...$s, ['label' => 'Mesaj', 'order' => 30, 'permission' => 'forms.manage',
    'points' => App\Services\Dashboard::daily('form_entries', 'created_at'), 'previous' => App\Services\Dashboard::previousTotal('form_entries', 'created_at')]]);

// Genel bakış
Hook::on('admin.dashboard.stats', fn(array $stats) => [...$stats,
    ['label' => 'Yeni mesaj', 'value' => Entry::unreadCount(), 'url' => 'admin/forms', 'order' => 30, 'permission' => 'forms.manage'],
]);

// Sayfa ve yazı içeriğinde [form iletisim] -> form (paragraf içinde tek başına da olabilir)
Hook::on('content.html', function (string $html) {
    if (!str_contains($html, '[form ')) return $html;
    return preg_replace_callback('#(?:<p>\s*)?\[form\s+([a-z0-9-]{1,60})\](?:\s*</p>)?#i', function ($m) {
        $form = Form::findBySlug(strtolower($m[1]));
        return $form ? (string) View::render('Forms::form', ['form' => $form]) : '';
    }, $html);
});
