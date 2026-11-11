<?php
// WhatsApp modülü: sitenin sayfa sonuna sabit düğme (modül aktifken yüklenir). Ayarları: settings.php
use App\Modules\Whatsapp\Services\WhatsappLink;

// ---- Sitede düğme
Hook::on('site.body_end', function (array $html) {
    if (!Setting::bool('whatsapp_enabled')) return $html;

    $scheme = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    $url = WhatsappLink::url($scheme . '://' . ($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '/'));
    if (!$url) return $html;   // numara yok ya da geçersiz: düğme gösterilmez

    return [...$html, (string) View::render('Whatsapp::button', [
        'url'      => $url,
        'label'    => (string) Setting::get('whatsapp_label'),
        'position' => Setting::get('whatsapp_position'),
        'showOn'   => Setting::get('whatsapp_show_on'),
    ])];
});
