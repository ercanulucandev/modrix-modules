<?php
namespace App\Modules\Newsletter\Controllers\Admin;

use App\Controllers\Admin\AdminController;
use App\Modules\Newsletter\Services\Newsletter;
use App\Services\Activity;
use Core\Database;
use Core\Request;
use Core\View;

// Admin > Bülten: aboneler, CSV, toplu e-posta
class SubscriberController extends AdminController
{
    public function index(): View
    {
        $status = (string) Request::get('status', '');
        $q = trim((string) Request::get('q', ''));
        $rows = Database::table('newsletter_subscribers')
            ->when(isset(Newsletter::STATUSES[$status]), fn($query) => $query->where('status', $status))
            ->search(['email'], $q)->orderByDesc('id')->paginate(50);
        return $this->view('Newsletter::admin/index', ['title' => 'Bülten', 'rows' => $rows, 'counts' => Newsletter::counts(), 'filter' => ['status' => $status, 'q' => $q]]);
    }

    public function destroy(string $id): void
    {
        $row = Database::table('newsletter_subscribers')->where('id', (int) $id)->first() ?? $this->abort(404);
        Database::table('newsletter_subscribers')->where('id', $row['id'])->delete();
        Activity::log('newsletter.deleted', "Bülten abonesini sildi: {$row['email']}", 'admin/newsletter');
        $this->redirect('admin/newsletter', 'Silindi: ' . $row['email']);
    }

    public function export(): never
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="bulten-aboneleri-' . date('Y-m-d') . '.csv"');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");   // Excel Türkçe karakterleri doğru açsın
        fputcsv($out, ['E-posta', 'Durum', 'Kayıt', 'Onay', 'Sayfa'], ';');
        foreach (Database::table('newsletter_subscribers')->orderBy('id')->get() as $r) {
            fputcsv($out, [$r['email'], Newsletter::STATUSES[$r['status']], $r['created_at'], $r['confirmed_at'], $r['source']], ';');
        }
        Activity::log('newsletter.export', 'Bülten abonelerini CSV olarak indirdi', 'admin/newsletter');
        exit;
    }

    public function compose(): View
    {
        return $this->view('Newsletter::admin/compose', ['title' => 'Bülten gönder', 'count' => Newsletter::counts()['active']]);
    }

    public function send(): void
    {
        $data = $this->validate(['subject' => 'required|max:150', 'message' => 'required|max:20000'], ['subject' => 'Konu', 'message' => 'Mesaj']);
        if (Request::post('test')) {
            $to = $this->admin()['email'];
            $ok = \Core\Mail::send($to, '[Deneme] ' . $data['subject'], $data['message'], ['html' => '<p>' . nl2br(View::e($data['message'])) . '</p>']);
            Session::flash('old', $data);
            $this->redirect('admin/newsletter/send', $ok ? "Deneme e-postası $to adresine gönderildi." : 'Gönderilemedi: ' . \Core\Mail::lastError(), $ok ? 'ok' : 'error');
        }
        [$sent, $failed] = Newsletter::send($data['subject'], $data['message']);
        Activity::log('newsletter.sent', "Bülten gönderdi: \"{$data['subject']}\" ($sent kişi" . ($failed ? ", $failed başarısız" : '') . ')', 'admin/newsletter');
        $this->redirect('admin/newsletter', "$sent aboneye gönderildi." . ($failed ? " $failed gönderilemedi (Ayarlar > E-posta'yı kontrol edin)." : ''), $failed && !$sent ? 'error' : 'ok');
    }
}
