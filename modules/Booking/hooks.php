<?php
// Randevu: hizmetler ve personel (koleksiyon), admin grubu, [booking], önbellek dışı adresler
use App\Modules\Booking\Services\Booking;
use App\Services\Collection;

Hook::on('admin.menu.groups', fn(array $g) => [...$g, 'booking' => ['label' => 'Randevular', 'icon' => 'calendar', 'order' => 22]]);

Collection::register('booking_services', [
    'label' => 'Hizmetler', 'singular' => 'Hizmet', 'icon' => 'briefcase', 'permission' => 'booking.manage', 'order' => 11, 'group' => 'booking',
    'description' => 'Randevu alınabilen hizmetler. Süre, boş saatlerin hesabında kullanılır.',
    'title' => 'name', 'subtitle' => 'duration', 'image' => 'image',
    'fields' => [
        'name'        => ['label' => 'Hizmet adı', 'type' => 'text', 'rules' => 'required|max:150'],
        'duration'    => ['label' => 'Süre (dakika)', 'type' => 'number', 'rules' => 'required|integer|between:5,600', 'default' => '30'],
        'price'       => ['label' => 'Fiyat (₺)', 'type' => 'number', 'rules' => 'nullable|numeric|min:0', 'help' => 'Bilgi amaçlı gösterilir; boşsa gösterilmez.'],
        'description' => ['label' => 'Açıklama', 'type' => 'textarea', 'rules' => 'nullable|max:1000'],
        'image'       => ['label' => 'Görsel', 'type' => 'image'],
    ],
]);
Collection::register('booking_staff', [
    'label' => 'Personel', 'singular' => 'Kişi', 'icon' => 'users', 'permission' => 'booking.manage', 'order' => 12, 'group' => 'booking',
    'description' => 'Randevu veren kişiler (berber, doktor, danışman...). Her kişi aynı anda bir randevu alır. Hiç kişi yoksa tek takvim kullanılır.',
    'title' => 'name', 'subtitle' => 'title', 'image' => 'photo',
    'fields' => [
        'name'     => ['label' => 'Ad soyad', 'type' => 'text', 'rules' => 'required|max:100'],
        'title'    => ['label' => 'Unvan', 'type' => 'text', 'rules' => 'nullable|max:100'],
        'photo'    => ['label' => 'Fotoğraf', 'type' => 'image'],
        'services' => ['label' => 'Verdiği hizmetler', 'type' => 'text', 'rules' => 'nullable|max:200', 'translatable' => false,
                       'help' => 'Hizmet numaraları virgülle (ör. 3, 5). Boş: tüm hizmetler. Numara, Hizmetler listesinde düzenle adresindeki sayıdır.'],
    ],
]);

Hook::on('cache.skip', fn(array $paths) => [...$paths, '/booking/']);

// [booking]: sayfa içinde randevu formu
Hook::on('content.html', function (string $html) {
    if (!str_contains($html, '[booking]')) return $html;
    return preg_replace('#(?:<p>\s*)?\[booking\](?:\s*</p>)?#i', (string) View::render('Booking::widget'), $html);
});

Hook::on('menu.links', fn(array $links) => [...$links, ['group' => 'Modül sayfaları', 'label' => Setting::get('booking_title', 'Randevu al'), 'url' => 'booking']]);
Hook::on('sitemap.urls', fn(array $urls) => [...$urls, ['loc' => 'booking', 'priority' => '0.6']]);
Hook::on('admin.dashboard.stats', fn(array $stats) => [...$stats,
    ['label' => 'Bugünkü randevu', 'value' => DB::table('booking_appointments')->whereIn('status', ['pending', 'confirmed'])
        ->where('starts_at', '>=', date('Y-m-d 00:00:00'))->where('starts_at', '<', date('Y-m-d 00:00:00', strtotime('+1 day')))->count(),
     'url' => 'admin/booking', 'order' => 3, 'permission' => 'booking.manage'],
]);
Hook::on('admin.menu', fn(array $menu) => array_map(fn($i) => $i['url'] === 'admin/booking'
    ? $i + ['badge' => DB::table('booking_appointments')->where('status', 'pending')->count() ?: null] : $i, $menu));

// Genel bakış: onay bekleyenler, grafik, yaklaşan randevular
Hook::on('admin.dashboard.todos', fn(array $t) => ($n = DB::table('booking_appointments')->where('status', 'pending')->count())
    ? [...$t, ['text' => "$n randevu onayınızı bekliyor.", 'url' => 'admin/booking?status=pending', 'tone' => 'warning', 'order' => 12, 'permission' => 'booking.manage']] : $t);
Hook::on('admin.dashboard.series', fn(array $s) => [...$s, ['label' => 'Randevu', 'order' => 20, 'permission' => 'booking.manage',
    'points' => App\Services\Dashboard::daily('booking_appointments', 'created_at', 'COUNT(*)', "status != 'cancelled'"),
    'previous' => App\Services\Dashboard::previousTotal('booking_appointments', 'created_at', 'COUNT(*)', "status != 'cancelled'")]]);
Hook::on('admin.dashboard.widgets', fn(array $w) => [...$w, ['order' => 6, 'permission' => 'booking.manage',
    'html' => (string) View::render('Booking::admin/widget', ['rows' => DB::table('booking_appointments')->whereIn('status', ['pending', 'confirmed'])
        ->where('starts_at', '>=', date('Y-m-d H:i:s'))->orderBy('starts_at')->limit(6)->get()])]]);
