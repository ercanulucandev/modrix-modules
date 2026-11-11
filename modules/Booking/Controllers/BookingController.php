<?php
namespace App\Modules\Booking\Controllers;

use App\Modules\Booking\Services\Booking;
use App\Services\Collection;
use Core\Controller;
use Core\HttpException;
use Core\Lang;
use Core\Request;
use Core\Setting;
use Core\View;

class BookingController extends Controller
{
    public function index(): View
    {
        return $this->view('Booking::index', ['title' => Setting::get('booking_title', 'Randevu al'), 'metaDescription' => Setting::get('booking_intro')]);
    }

    // GET /booking/slots?service=3&date=2026-11-20&staff=5  ->  { slots: ["09:00", ...] }
    public function slots(): string
    {
        $service = Collection::find('booking_services', (int) Request::get('service'));
        if (!$service || !$service['visible']) return $this->json(['slots' => []]);
        $slots = Booking::slots($service, (string) Request::get('date', ''), ((int) Request::get('staff')) ?: null);
        header('Cache-Control: no-store');
        return $this->json(['slots' => array_keys($slots)]);
    }

    public function store(): void
    {
        $data = $this->validate([
            'service_id' => 'required|integer',
            'staff_id'   => 'nullable|integer',
            'date'       => 'required|date',
            'time'       => 'required|regex:/^\d{2}:\d{2}$/',
            'name'       => 'required|max:100',
            'email'      => 'required|email|max:190',
            'phone'      => 'required|phone',
            'note'       => 'nullable|max:1000',
        ], ['service_id' => Lang::t('Hizmet'), 'date' => Lang::t('Tarih'), 'time' => Lang::t('Saat'), 'name' => Lang::t('Ad soyad'), 'email' => Lang::t('E-posta'), 'phone' => Lang::t('Telefon')]);
        if (trim((string) Request::post('website', '')) !== '') $this->abort(422);   // bot tuzağı

        try {
            $a = Booking::create($data);
        } catch (HttpException $e) {
            $this->back($e->getMessage(), 'error');
        }
        $this->redirect('booking/' . $a['token'], $a['status'] === 'pending' ? Lang::t('Randevu talebiniz alındı; onaylanınca e-postayla bildireceğiz.') : Lang::t('Randevunuz oluşturuldu.'));
    }

    public function show(string $token): View
    {
        $a = Booking::findByToken($token) ?? $this->abort(404, Lang::t('Randevu bulunamadı'));
        return $this->view('Booking::show', ['title' => Lang::t('Randevunuz'), 'a' => $a]);
    }

    public function cancel(string $token): void
    {
        $a = Booking::findByToken($token) ?? $this->abort(404);
        if (!Booking::canCancel($a)) $this->redirect("booking/$token", Lang::t('Bu randevu artık sitemizden iptal edilemiyor; lütfen bizi arayın.'), 'error');
        Booking::setStatus($a, 'cancelled', true);
        $this->redirect("booking/$token", Lang::t('Randevunuz iptal edildi.'));
    }
}
