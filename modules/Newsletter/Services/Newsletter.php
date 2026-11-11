<?php
namespace App\Modules\Newsletter\Services;

use App\Services\Crypt;
use Core\Database;
use Core\HttpException;
use Core\Mail;
use Core\Request;
use Core\Router;
use Core\Setting;
use Core\View;

final class Newsletter
{
    private const RATE_LIMIT = 5;     // IP başına 10 dakikada
    private const MIN_SECONDS = 2;

    public const STATUSES = ['active' => 'Abone', 'pending' => 'Onay bekliyor', 'unsubscribed' => 'Ayrıldı'];

    public static function timeToken(): string
    {
        $t = (string) time();
        return $t . '.' . Crypt::sign("newsletter:$t");
    }

    // Formdan abonelik. Dönüş: ziyaretçiye gösterilecek mesaj. Hata: HttpException (mesajı gösterilir)
    public static function subscribe(string $email, string $source): string
    {
        self::guard();
        $email = mb_strtolower(trim($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) throw new HttpException(422, 'Geçerli bir e-posta adresi yazın.');
        if (Setting::get('newsletter_consent') && !Request::post('consent')) throw new HttpException(422, 'Devam etmek için onay kutusunu işaretleyin.');

        $double = Setting::bool('newsletter_double_optin');
        $row = Database::table('newsletter_subscribers')->where('email', $email)->first();
        if ($row && $row['status'] === 'active') return 'Bu adres zaten abone. Teşekkürler!';

        $token = bin2hex(random_bytes(16));
        $values = ['status' => $double ? 'pending' : 'active', 'token' => $token, 'ip' => Request::ip(), 'source' => mb_substr($source, 0, 255),
                   'confirmed_at' => $double ? null : date('Y-m-d H:i:s'), 'unsubscribed_at' => null];
        $row ? Database::table('newsletter_subscribers')->where('id', $row['id'])->update($values)
             : Database::table('newsletter_subscribers')->insert($values + ['email' => $email, 'created_at' => date('Y-m-d H:i:s')]);

        if (!$double) return (string) Setting::get('newsletter_success', 'Teşekkürler! Bültenimize abone oldunuz.');

        $site = (string) Setting::get('site_name', 'Site');
        $link = self::absolute(Router::url('newsletter.confirm', ['token' => $token]));
        Mail::send($email, "Aboneliğinizi onaylayın · $site",
            "Merhaba,\n\n$site bültenine abone olmak için bu bağlantıya tıklayın:\n$link\n\nBu isteği siz yapmadıysanız bu e-postayı yok sayın.\n",
            ['html' => '<p>Merhaba,</p><p><strong>' . View::e($site) . '</strong> bültenine abone olmak için aşağıdaki düğmeye tıklayın.</p>'
                     . '<p><a href="' . View::e($link) . '" style="display:inline-block;padding:10px 18px;background:#18181b;color:#fff;border-radius:6px;text-decoration:none">Aboneliğimi onayla</a></p>'
                     . '<p style="color:#71717a;font-size:13px">Bu isteği siz yapmadıysanız bu e-postayı yok sayın.</p>']);
        return 'Neredeyse tamam! E-postanıza gelen bağlantıyla aboneliğinizi onaylayın.';
    }

    public static function confirm(string $token): ?array
    {
        $row = self::byToken($token);
        if (!$row || $row['status'] === 'unsubscribed') return null;
        if ($row['status'] === 'pending') Database::table('newsletter_subscribers')->where('id', $row['id'])->update(['status' => 'active', 'confirmed_at' => date('Y-m-d H:i:s')]);
        return $row;
    }

    public static function unsubscribe(string $token): ?array
    {
        $row = self::byToken($token);
        if ($row) Database::table('newsletter_subscribers')->where('id', $row['id'])->update(['status' => 'unsubscribed', 'unsubscribed_at' => date('Y-m-d H:i:s')]);
        return $row;
    }

    public static function byToken(string $token): ?array
    {
        return preg_match('/^[a-f0-9]{32}$/', $token) ? Database::table('newsletter_subscribers')->where('token', $token)->first() : null;
    }

    public static function counts(): array
    {
        $counts = array_fill_keys(array_keys(self::STATUSES), 0);
        foreach (Database::all('SELECT status, COUNT(*) AS n FROM newsletter_subscribers GROUP BY status') as $r) $counts[$r['status']] = (int) $r['n'];
        return $counts;
    }

    // Abonelere toplu e-posta. Dönüş: [gönderilen, başarısız]
    public static function send(string $subject, string $message): array
    {
        @set_time_limit(0);
        $site = (string) Setting::get('site_name', 'Site');
        $paragraphs = array_filter(preg_split('/\R{2,}/', trim($message)), 'strlen');
        $html = implode('', array_map(fn($p) => '<p>' . nl2br(View::e($p)) . '</p>', $paragraphs));
        $sent = $failed = 0;
        foreach (Database::table('newsletter_subscribers')->where('status', 'active')->orderBy('id')->get() as $s) {
            $unsub = self::absolute(Router::url('newsletter.unsubscribe', ['token' => $s['token']]));
            $ok = Mail::send($s['email'], $subject,
                trim($message) . "\n\n--\n$site bültenine abone olduğunuz için bu e-postayı aldınız. Ayrılmak için: $unsub\n",
                ['html' => $html . '<hr style="border:0;border-top:1px solid #e4e4e7;margin:24px 0"><p style="color:#71717a;font-size:12px">'
                         . View::e($site) . ' bültenine abone olduğunuz için bu e-postayı aldınız. <a href="' . View::e($unsub) . '">Abonelikten çık</a></p>']);
            $ok ? $sent++ : $failed++;
        }
        return [$sent, $failed];
    }

    public static function absolute(string $url): string
    {
        $scheme = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
        return str_starts_with($url, 'http') ? $url : $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $url;
    }

    // Spam koruması: gizli tuzak alanı, imzalı zaman damgası, IP başına sınır
    private static function guard(): void
    {
        if (trim((string) Request::post('website', '')) !== '') throw new HttpException(422, 'Gönderim reddedildi.');
        [$t, $sig] = array_pad(explode('.', (string) Request::post('_t', ''), 2), 2, '');
        if (!ctype_digit($t) || !hash_equals(Crypt::sign("newsletter:$t"), $sig)) throw new HttpException(422, 'Form süresi doldu; sayfayı yenileyip tekrar deneyin.');
        if (time() - (int) $t < self::MIN_SECONDS) throw new HttpException(422, 'Çok hızlı gönderildi; lütfen tekrar deneyin.');
        $recent = Database::table('newsletter_subscribers')->where('ip', Request::ip())->where('created_at', '>', date('Y-m-d H:i:s', time() - 600))->count();
        if ($recent >= self::RATE_LIMIT) throw new HttpException(429, 'Çok fazla deneme; birkaç dakika sonra tekrar deneyin.');
    }
}
