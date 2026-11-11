<?php
namespace App\Modules\Forms\Services;

use App\Modules\Forms\Models\Entry;
use App\Modules\Forms\Models\Form;
use Core\HttpException;
use Core\Log;
use Core\Mail;
use Core\Request;
use Core\Session;
use Core\Setting;
use Core\Validator;

// Formların iş mantığı: alan tanımlarını temizleme, gönderimi doğrulama, spam koruması, kayıt ve e-posta bildirimi.
final class FormService
{
    public const TYPES = [
        'text' => 'Kısa yazı', 'email' => 'E-posta', 'tel' => 'Telefon', 'number' => 'Sayı', 'date' => 'Tarih',
        'textarea' => 'Uzun yazı', 'select' => 'Açılır liste', 'radio' => 'Tek seçim', 'checkbox' => 'Onay kutusu',
    ];
    private const MIN_SECONDS = 3;        // formu bu süreden hızlı gönderen bot sayılır
    private const RATE_LIMIT = 5;         // aynı IP'den 10 dakikada en fazla
    private const MAX_FIELDS = 30;

    // ================================================================ alan tanımları

    // Oluşturucudan gelen alan listesini temizler
    public static function cleanFields($fields): array
    {
        if (!is_array($fields)) Validator::fail(['fields' => 'Alanlar geçersiz.']);
        $out = [];
        $names = [];
        foreach (array_slice($fields, 0, self::MAX_FIELDS) as $f) {
            if (!is_array($f)) continue;
            $label = trim((string) ($f['label'] ?? ''));
            if ($label === '') continue;
            $type = isset(self::TYPES[$f['type'] ?? '']) ? $f['type'] : 'text';
            $name = self::slug((string) (($f['name'] ?? '') ?: $label), '_') ?: 'alan';
            for ($i = 2, $base = $name; isset($names[$name]); $i++) $name = "{$base}_$i";
            $names[$name] = true;

            $options = array_values(array_filter(array_map(fn($o) => mb_substr(trim((string) $o), 0, 120), (array) ($f['options'] ?? [])), 'strlen'));
            if (in_array($type, ['select', 'radio'], true) && !$options) $options = ['Seçenek 1', 'Seçenek 2'];

            $out[] = [
                'name'        => $name,
                'label'       => mb_substr($label, 0, 300),
                'type'        => $type,
                'required'    => !empty($f['required']),
                'placeholder' => mb_substr(trim((string) ($f['placeholder'] ?? '')), 0, 120),
                'options'     => in_array($type, ['select', 'radio'], true) ? array_slice($options, 0, 50) : [],
                'width'       => ($f['width'] ?? 'full') === 'half' ? 'half' : 'full',
                'help'        => mb_substr(trim((string) ($f['help'] ?? '')), 0, 200),
            ];
        }
        if (!$out) Validator::fail(['fields' => 'En az bir alan ekleyin.']);
        return $out;
    }

    public static function cleanSettings($s): array
    {
        $s = is_array($s) ? $s : [];
        $emails = array_filter(array_map('trim', explode(',', (string) ($s['email_to'] ?? ''))));
        foreach ($emails as $e) if (!filter_var($e, FILTER_VALIDATE_EMAIL)) Validator::fail(['email_to' => "\"$e\" geçerli bir e-posta adresi değil."]);
        return [
            'email_to'        => implode(', ', $emails),
            'subject'         => mb_substr(trim(str_replace(["\r", "\n"], ' ', (string) ($s['subject'] ?? ''))), 0, 150) ?: 'Yeni form mesajı',
            'success_message' => mb_substr(trim((string) ($s['success_message'] ?? '')), 0, 500) ?: 'Teşekkürler! Mesajınız bize ulaştı.',
            'button_text'     => mb_substr(trim((string) ($s['button_text'] ?? '')), 0, 40) ?: 'Gönder',
            'store'           => !array_key_exists('store', $s) || !empty($s['store']),
        ];
    }

    public static function uniqueSlug(string $text, ?int $exceptId = null): string
    {
        $base = self::slug($text, '-') ?: 'form';
        $slug = $base;
        for ($i = 2; ($row = Form::query()->where('slug', $slug)->first()) && (int) $row['id'] !== $exceptId; $i++) $slug = "$base-$i";
        return $slug;
    }

    public static function slug(string $text, string $sep): string
    {
        $text = strtolower(strtr($text, ['ç' => 'c', 'Ç' => 'c', 'ğ' => 'g', 'Ğ' => 'g', 'ı' => 'i', 'İ' => 'i', 'ö' => 'o', 'Ö' => 'o', 'ş' => 's', 'Ş' => 's', 'ü' => 'u', 'Ü' => 'u']));
        return substr(trim(preg_replace('/[^a-z0-9]+/', $sep, $text), $sep), 0, 50);
    }

    // ================================================================ gönderim

    // Formun gizli alanlarına: zaman damgası (sunucu anahtarıyla imzalı; sayfa önbelleğinde de geçerli). Botları ayıklamak için.
    public static function timeToken(): string
    {
        $t = (string) time();
        return $t . '.' . \App\Services\Crypt::sign("form:$t");
    }

    // Doğrular, kaydeder, bildirir. Dönüş: başarı mesajı. Hata: Validator::fail (422) ya da HttpException
    public static function submit(array $form): string
    {
        self::guard($form);

        $data = [];
        $errors = [];
        foreach ($form['fields'] as $f) {
            $raw = Request::post($f['name']);
            $value = is_scalar($raw) ? trim((string) $raw) : '';
            $label = $f['label'];

            if ($f['type'] === 'checkbox') {
                $checked = $value !== '' && $value !== '0';
                if ($f['required'] && !$checked) $errors[$f['name']] = 'Devam etmek için işaretlemelisiniz.';
                $data[$f['name']] = ['label' => $label, 'value' => $checked ? 'Evet' : 'Hayır'];
                continue;
            }
            if ($value === '') {
                if ($f['required']) $errors[$f['name']] = 'Bu alan zorunludur.';
                $data[$f['name']] = ['label' => $label, 'value' => ''];
                continue;
            }

            $max = $f['type'] === 'textarea' ? 5000 : 300;
            $error = match (true) {
                mb_strlen($value) > $max                                                  => "En fazla $max karakter olabilir.",
                $f['type'] === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)       => 'Geçerli bir e-posta adresi yazın.',
                $f['type'] === 'tel' && !preg_match('/^[0-9 +()\-]{7,20}$/', $value)       => 'Geçerli bir telefon numarası yazın.',
                $f['type'] === 'number' && !is_numeric($value)                            => 'Sayı yazın.',
                $f['type'] === 'date' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)      => 'Geçerli bir tarih seçin.',
                in_array($f['type'], ['select', 'radio'], true) && !in_array($value, $f['options'], true) => 'Listeden bir seçenek seçin.',
                default => null,
            };
            if ($error) $errors[$f['name']] = $error;
            $data[$f['name']] = ['label' => $label, 'value' => $value];
        }
        if ($errors) Validator::fail($errors);

        $page = (string) ($_SERVER['HTTP_REFERER'] ?? '');
        if (!str_starts_with($page, 'http')) $page = '';

        if ($form['settings']['store']) {
            Entry::create([
                'form_id'    => $form['id'],
                'data'       => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE),
                'ip'         => Request::ip(),
                'user_agent' => mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
                'page_url'   => mb_substr($page, 0, 500),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
        self::notify($form, $data, $page);
        \Core\Hook::fire('form.submitted', $form, $data);

        return $form['settings']['success_message'];
    }

    // Spam koruması: gizli tuzak alanı, çok hızlı gönderim, IP başına sınır
    private static function guard(array $form): void
    {
        // Gerçek kişi görmez; bot doldurur
        if (trim((string) Request::post('website', '')) !== '') throw new HttpException(422, 'Gönderim reddedildi.');

        [$t, $sig] = array_pad(explode('.', (string) Request::post('_t', ''), 2), 2, '');
        $valid = ctype_digit($t) && hash_equals(\App\Services\Crypt::sign("form:$t"), $sig);
        if (!$valid) throw new HttpException(422, 'Form süresi doldu; sayfayı yenileyip tekrar deneyin.');
        if (time() - (int) $t < self::MIN_SECONDS) throw new HttpException(422, 'Çok hızlı gönderildi; lütfen tekrar deneyin.');

        $recent = Entry::query()->where('form_id', $form['id'])->where('ip', Request::ip())
            ->where('created_at', '>', date('Y-m-d H:i:s', time() - 600))->count();
        if ($recent >= self::RATE_LIMIT) throw new HttpException(429, 'Çok fazla gönderim yaptınız; birkaç dakika sonra tekrar deneyin.');
    }

    // E-posta bildirimi (Ayarlar > E-posta). Gönderilemezse mesaj yine kutuda durur, hata log'a yazılır.
    private static function notify(array $form, array $data, string $page): void
    {
        $to = $form['settings']['email_to'] ?: (string) Setting::get('forms_notify_email') ?: (string) Setting::get('contact_email');
        if ($to === '') return;

        $site = (string) Setting::get('site_name', 'Site');
        $lines = [];
        foreach ($data as $item) $lines[] = $item['label'] . ":\n" . ($item['value'] !== '' ? $item['value'] : '—') . "\n";
        $body = $form['name'] . " formundan yeni mesaj\n\n" . implode("\n", $lines) . ($page ? "\nSayfa: $page" : '') . "\nTarih: " . date('d.m.Y H:i');

        // Yanıtla -> formu dolduran kişi (ilk geçerli e-posta alanı)
        $replyTo = null;
        foreach ($form['fields'] as $f) {
            $v = $data[$f['name']]['value'] ?? '';
            if ($f['type'] === 'email' && filter_var($v, FILTER_VALIDATE_EMAIL)) { $replyTo = $v; break; }
        }
        $recipients = array_filter(array_map('trim', explode(',', $to)));
        Mail::send($recipients, $form['settings']['subject'] . ' · ' . $site, $body, ['reply_to' => $replyTo]);   // hata log'a yazılır
    }
}
