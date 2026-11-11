<?php
namespace App\Modules\Booking\Controllers\Admin;

use App\Controllers\Admin\AdminController;
use App\Modules\Booking\Services\Booking;
use App\Services\Activity;
use Core\Database;
use Core\Request;
use Core\View;

// Admin > Randevular
class AppointmentController extends AdminController
{
    public function index(): View
    {
        $view = in_array($v = (string) Request::get('view', 'upcoming'), ['upcoming', 'today', 'past', 'all'], true) ? $v : 'upcoming';
        $status = (string) Request::get('status', '');
        $q = trim((string) Request::get('q', ''));
        $today = date('Y-m-d 00:00:00');
        $rows = Database::table('booking_appointments')
            ->when($view === 'upcoming', fn($x) => $x->where('starts_at', '>=', $today))
            ->when($view === 'today', fn($x) => $x->where('starts_at', '>=', $today)->where('starts_at', '<', date('Y-m-d 00:00:00', strtotime('+1 day'))))
            ->when($view === 'past', fn($x) => $x->where('starts_at', '<', $today))
            ->when(isset(Booking::STATUSES[$status]), fn($x) => $x->where('status', $status))
            ->search(['name', 'email', 'phone', 'service_name'], $q)
            ->when($view === 'past' || $view === 'all', fn($x) => $x->orderByDesc('starts_at'), fn($x) => $x->orderBy('starts_at'))
            ->paginate(50);
        return $this->view('Booking::admin/index', ['title' => 'Randevular', 'rows' => $rows, 'filter' => compact('view', 'status', 'q'),
            'pending' => Database::table('booking_appointments')->where('status', 'pending')->count()]);
    }

    public function show(string $id): View
    {
        $a = Booking::find((int) $id) ?? $this->abort(404);
        return $this->view('Booking::admin/show', ['title' => 'Randevu', 'a' => $a]);
    }

    public function update(string $id): void
    {
        $a = Booking::find((int) $id) ?? $this->abort(404);
        $data = $this->validate(['status' => 'required|in:' . implode(',', array_keys(Booking::STATUSES)), 'admin_note' => 'nullable|max:1000'], ['status' => 'Durum']);
        Booking::setStatus($a, $data['status'], (bool) Request::post('notify'), (string) ($data['admin_note'] ?? ''));
        Activity::log('booking.updated', "Randevuyu güncelledi: {$a['name']} · " . Booking::when($a['starts_at']) . ' · ' . Booking::STATUSES[$data['status']], "admin/booking/{$a['id']}");
        $this->redirect("admin/booking/{$a['id']}", 'Randevu güncellendi.');
    }
}
