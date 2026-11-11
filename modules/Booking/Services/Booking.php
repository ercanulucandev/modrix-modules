<?php
namespace App\Modules\Booking\Services;

use App\Services\Collection;
use Core\Database;
use Core\Hook;
use Core\HttpException;
use Core\Lang;
use Core\Log;
use Core\Mail;
use Core\Request;
use Core\Router;
use Core\Setting;
use Core\View;
use DateTimeImmutable;

// Randevu kuralları: çalışma saatleri, boş saatler, oluşturma (çakışma kilidi), iptal, e-postalar
final class Booking
{
    public const STATUSES = ['pending' => 'Onay bekliyor', 'confirmed' => 'Onaylandı', 'completed' => 'Tamamlandı', 'cancelled' => 'İptal edildi', 'no_show' => 'Gelmedi'];
    private const ACTIVE = ['pending', 'confirmed'];   // saati dolduranlar

    public static function services(): array
    {
        return Collection::items('booking_services');
    }

    public static function staff(): array
    {
        return Collection::items('booking_staff');
    }

    // Personel bu hizmeti veriyor mu (personelde hizmet seçilmemişse hepsini verir)
    public static function staffFor(int $serviceId): array
    {
        return array_values(array_filter(self::staff(), function ($s) use ($serviceId) {
            $ids = array_filter(array_map('intval', preg_split('/[,\s]+/', (string) ($s['services'] ?? ''))));
            return !$ids || in_array($serviceId, $ids, true);
        }));
    }

    // Günün çalışma aralıkları: [['09:00', '12:00'], ['13:00', '18:00']] (kapalıysa [])
    public static function hours(DateTimeImmutable $day): array
    {
        if (in_array($day->format('Y-m-d'), self::closedDates(), true)) return [];
        $spec = trim((string) Setting::get('booking_hours_' . $day->format('N')));
        $out = [];
        foreach (array_filter(array_map('trim', explode(',', $spec))) as $range) {
            if (preg_match('/^(\d{2}:\d{2})-(\d{2}:\d{2})$/', $range, $m) && $m[1] < $m[2]) $out[] = [$m[1], $m[2]];
        }
        return $out;
    }

    // Seçilebilir günler: bugünden itibaren 'booking_days_ahead' gün, açık olanlar
    public static function days(): array
    {
        $days = [];
        $today = new DateTimeImmutable('today');
        for ($i = 0, $n = max(1, (int) Setting::get('booking_days_ahead', 30)); $i < $n; $i++) {
            $d = $today->modify("+$i day");
            if (self::hours($d)) $days[] = $d->format('Y-m-d');
        }
        return $days;
    }

    // Boş başlangıç saatleri: ['09:00' => [personel id'leri], ...]; $staffId verilirse sadece o kişi
    public static function slots(array $service, string $date, ?int $staffId = null): array
    {
        $day = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        if (!$day || !in_array($date, self::days(), true)) return [];
        $duration = max(5, (int) $service['duration']);
        $interval = max(5, (int) Setting::get('booking_interval', 30));
        $earliest = (new DateTimeImmutable())->modify('+' . (int) Setting::get('booking_notice', 2) . ' hours');

        // Kaynaklar: personel (hizmeti veren); personel tanımlı değilse tek kaynak (0)
        $people = $staffId ? array_filter(self::staffFor((int) $service['id']), fn($s) => $s['id'] === $staffId) : self::staffFor((int) $service['id']);
        $resources = self::staff() ? array_column($people, 'id') : [0];
        if (!$resources) return [];

        $busy = [];
        foreach (Database::table('booking_appointments')->whereIn('status', self::ACTIVE)
            ->where('starts_at', '<', $day->modify('+1 day')->format('Y-m-d H:i:s'))->where('ends_at', '>', $day->format('Y-m-d H:i:s'))->get() as $a) {
            $busy[(int) $a['staff_id']][] = [strtotime($a['starts_at']), strtotime($a['ends_at'])];
        }

        $slots = [];
        foreach (self::hours($day) as [$open, $close]) {
            $start = strtotime("$date $open");
            $end = strtotime("$date $close");
            for ($t = $start; $t + $duration * 60 <= $end; $t += $interval * 60) {
                if ($t < $earliest->getTimestamp()) continue;
                $free = array_values(array_filter($resources, function ($r) use ($busy, $t, $duration) {
                    foreach ($busy[$r] ?? [] as [$s, $e]) if ($t < $e && $t + $duration * 60 > $s) return false;
                    return true;
                }));
                if ($free) $slots[date('H:i', $t)] = $free;
            }
        }
        return $slots;
    }

    // Randevu oluşturur. Aynı anda iki kişi aynı saati alamasın diye kilitli.
    public static function create(array $data): array
    {
        $service = Collection::find('booking_services', (int) $data['service_id']);
        if (!$service || !$service['visible']) throw new HttpException(422, Lang::t('Hizmet bulunamadı.'));
        $staffId = (int) ($data['staff_id'] ?? 0) ?: null;

        Database::value("SELECT GET_LOCK('modrix_booking', 10)");
        try {
            $slots = self::slots($service, $data['date'], $staffId);
            if (!isset($slots[$data['time']])) throw new HttpException(422, Lang::t('Bu saat az önce doldu; lütfen başka bir saat seçin.'));
            $resource = $slots[$data['time']][0];   // seçilmediyse ilk boş personel
            $staff = $resource ? Collection::find('booking_staff', (int) $resource) : null;
            $start = new DateTimeImmutable("{$data['date']} {$data['time']}");
            $id = Database::table('booking_appointments')->insertGetId([
                'token' => bin2hex(random_bytes(16)), 'service_id' => $service['id'], 'service_name' => $service['name'],
                'staff_id' => $staff['id'] ?? null, 'staff_name' => $staff['name'] ?? null,
                'starts_at' => $start->format('Y-m-d H:i:s'), 'ends_at' => $start->modify('+' . (int) $service['duration'] . ' minutes')->format('Y-m-d H:i:s'),
                'status' => Setting::bool('booking_auto_confirm') ? 'confirmed' : 'pending',
                'price' => ($service['price'] ?? '') !== '' ? number_format((float) $service['price'], 2, '.', '') : null,
                'name' => $data['name'], 'email' => mb_strtolower($data['email']), 'phone' => $data['phone'], 'note' => $data['note'] ?: null,
                'ip' => Request::ip(), 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } finally {
            Database::value("SELECT RELEASE_LOCK('modrix_booking')");
        }

        $a = self::find($id);
        Hook::fire('booking.created', $a);
        self::mail($a, 'created');
        self::notifyAdmin($a);
        return $a;
    }

    public static function find(int $id): ?array
    {
        return Database::table('booking_appointments')->where('id', $id)->first();
    }

    public static function findByToken(string $token): ?array
    {
        return preg_match('/^[a-f0-9]{32}$/', $token) ? Database::table('booking_appointments')->where('token', $token)->first() : null;
    }

    public static function canCancel(array $a): bool
    {
        $hours = (int) Setting::get('booking_cancel_hours', 24);
        return $hours > 0 && in_array($a['status'], self::ACTIVE, true) && strtotime($a['starts_at']) - time() >= $hours * 3600;
    }

    public static function setStatus(array $a, string $status, bool $notify = false, ?string $adminNote = null): array
    {
        if (!isset(self::STATUSES[$status])) throw new HttpException(422, 'Geçersiz durum.');
        Database::table('booking_appointments')->where('id', $a['id'])->update(['status' => $status, 'updated_at' => date('Y-m-d H:i:s')] + ($adminNote !== null ? ['admin_note' => $adminNote ?: null] : []));
        $updated = self::find($a['id']);
        Hook::fire('booking.status', $updated, $a['status']);
        if ($notify && $status !== $a['status']) self::mail($updated, 'status');
        return $updated;
    }

    public static function url(array $a): string
    {
        $url = Router::url('booking.show', ['token' => $a['token']]);
        $scheme = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
        return $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $url;
    }

    // 2026-11-20 14:30:00 -> "20 Kasım 2026 Cuma, 14:30"
    public static function when(string $datetime): string
    {
        $months = ['', 'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
        $daysTr = ['', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi', 'Pazar'];
        $t = strtotime($datetime);
        return (int) date('j', $t) . ' ' . Lang::t($months[(int) date('n', $t)]) . ' ' . date('Y', $t) . ' ' . Lang::t($daysTr[(int) date('N', $t)]) . ', ' . date('H:i', $t);
    }

    private static function mail(array $a, string $event): void
    {
        $site = (string) Setting::get('site_name', 'Site');
        $subject = match (true) {
            $event === 'created' && $a['status'] === 'pending' => Lang::t('Randevu talebiniz alındı'),
            $event === 'created'                              => Lang::t('Randevunuz oluşturuldu'),
            $a['status'] === 'confirmed'                      => Lang::t('Randevunuz onaylandı'),
            $a['status'] === 'cancelled'                      => Lang::t('Randevunuz iptal edildi'),
            default                                           => Lang::t('Randevunuz güncellendi'),
        };
        $html = (string) View::render('Booking::emails/appointment', ['a' => $a, 'subject' => $subject, 'site' => $site, 'url' => self::url($a)]);
        if (!Mail::send($a['email'], "$subject · $site", trim(html_entity_decode(strip_tags(str_replace(['<br>', '</p>'], "\n", $html)))), ['html' => $html])) {
            Log::warning('Randevu e-postası gönderilemedi', ['id' => $a['id'], 'error' => Mail::lastError()]);
        }
    }

    private static function notifyAdmin(array $a): void
    {
        $to = (string) (Setting::get('booking_notify_email') ?: Setting::get('contact_email'));
        if ($to === '') return;
        Mail::send($to, 'Yeni randevu: ' . $a['service_name'] . ' · ' . self::when($a['starts_at']),
            "Hizmet: {$a['service_name']}" . ($a['staff_name'] ? " ({$a['staff_name']})" : '') . "\nZaman: " . self::when($a['starts_at'])
            . "\nMüşteri: {$a['name']} · {$a['phone']} · {$a['email']}" . ($a['note'] ? "\nNot: {$a['note']}" : '')
            . "\nDurum: " . self::STATUSES[$a['status']] . "\n\nPanel: " . preg_replace('#/booking/.*$#', '/admin/booking/' . $a['id'], self::url($a)) . "\n",
            ['reply_to' => $a['email']]);
    }

    private static function closedDates(): array
    {
        static $dates = null;
        return $dates ??= array_values(array_filter(array_map('trim', preg_split('/\R/', (string) Setting::get('booking_closed'))), fn($d) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)));
    }
}
