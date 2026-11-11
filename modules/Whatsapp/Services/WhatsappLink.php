<?php
namespace App\Modules\Whatsapp\Services;

use Core\Setting;

// WhatsApp "tıkla sohbet et" bağlantısı (https://wa.me/905321234567?text=...)
final class WhatsappLink
{
    // "0532 123 45 67", "+90 (532) 123-45-67", "5321234567" -> "905321234567"  (Türkiye varsayılır)
    // Geçersizse null
    public static function normalize(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if (strlen($digits) === 11 && str_starts_with($digits, '0')) $digits = '90' . substr($digits, 1);   // 0532...
        if (strlen($digits) === 10 && str_starts_with($digits, '5')) $digits = '90' . $digits;              // 532...
        if (str_starts_with($digits, '00')) $digits = substr($digits, 2);                                   // 0090...

        return preg_match('/^[1-9]\d{9,14}$/', $digits) ? $digits : null;
    }

    // Ayarlardaki numara ve mesajla bağlantı; {url} bulunulan sayfanın adresiyle değişir
    public static function url(string $currentUrl): ?string
    {
        $phone = self::normalize(Setting::get('whatsapp_phone'));
        if (!$phone) return null;

        $message = trim(strtr((string) Setting::get('whatsapp_message'), ['{url}' => $currentUrl]));
        return 'https://wa.me/' . $phone . ($message !== '' ? '?text=' . rawurlencode($message) : '');
    }
}
