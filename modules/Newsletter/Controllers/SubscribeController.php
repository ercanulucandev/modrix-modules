<?php
namespace App\Modules\Newsletter\Controllers;

use App\Modules\Newsletter\Services\Newsletter;
use Core\Controller;
use Core\HttpException;
use Core\Request;
use Core\Session;
use Core\View;

class SubscribeController extends Controller
{
    public function store(): string
    {
        $back = self::backUrl();
        try {
            $message = Newsletter::subscribe((string) Request::post('email', ''), $back);
            $ok = true;
        } catch (HttpException $e) {
            [$message, $ok] = [$e->getMessage(), false];
        }
        if (Request::isAjax()) return $this->json(['message' => $message], $ok ? 200 : 422);
        Session::flash('newsletter', ['ok' => $ok, 'message' => $message]);
        if (!$ok) Session::flash('newsletter_email', (string) Request::post('email', ''));
        header('Location: ' . $back . '#bulten');
        exit;
    }

    public function confirm(string $token): View
    {
        $row = Newsletter::confirm($token);
        return $this->view('Newsletter::message', $row
            ? ['title' => 'Abonelik onaylandı', 'message' => 'Teşekkürler! ' . $row['email'] . ' adresi bültenimize eklendi.']
            : ['title' => 'Bağlantı geçersiz', 'message' => 'Bu onay bağlantısı geçersiz ya da abonelik sonlandırılmış.']);
    }

    // Bağlantıya tıklayınca onay sorulur (e-posta tarayıcıları bağlantıları önceden açabilir; tek tıkla çıkmasın)
    public function unsubscribeForm(string $token): View
    {
        $row = Newsletter::byToken($token);
        return $this->view('Newsletter::unsubscribe', ['title' => 'Abonelikten çık', 'row' => $row, 'token' => $token]);
    }

    public function unsubscribe(string $token): View
    {
        $row = Newsletter::unsubscribe($token);
        return $this->view('Newsletter::message', $row
            ? ['title' => 'Abonelikten çıktınız', 'message' => $row['email'] . ' adresine artık bülten gönderilmeyecek.']
            : ['title' => 'Bağlantı geçersiz', 'message' => 'Bu bağlantı geçersiz.']);
    }

    // Formun gönderildiği sayfa (sadece aynı site)
    private static function backUrl(): string
    {
        $ref = (string) ($_SERVER['HTTP_REFERER'] ?? '');
        $host = parse_url($ref, PHP_URL_HOST);
        if ($ref !== '' && $host === ($_SERVER['HTTP_HOST'] ?? null)) {
            return preg_replace('/#.*$/', '', (string) (parse_url($ref, PHP_URL_PATH) ?: '/') . (($q = parse_url($ref, PHP_URL_QUERY)) ? "?$q" : ''));
        }
        return Request::url('/');
    }
}
