<?php
// Randevu: /booking (form), /booking/slots (boş saatler, JSON), /booking/{token} (müşterinin randevusu); Admin > Randevular
use App\Modules\Booking\Controllers\Admin\AppointmentController;
use App\Modules\Booking\Controllers\BookingController;

Router::get('/booking', [BookingController::class, 'index'])->name('booking.index');
Router::get('/booking/slots', [BookingController::class, 'slots'])->name('booking.slots');
Router::post('/booking', [BookingController::class, 'store'])->name('booking.store');
Router::get('/booking/{token}', [BookingController::class, 'show'])->name('booking.show');
Router::post('/booking/{token}/cancel', [BookingController::class, 'cancel'])->name('booking.cancel');

Router::group(['prefix' => '/admin/booking', 'middleware' => ['admin', 'can:booking.manage']], function () {
    Router::get('/', [AppointmentController::class, 'index'])->name('admin.booking');
    Router::get('/{id}', [AppointmentController::class, 'show'])->name('admin.booking.show');
    Router::post('/{id}', [AppointmentController::class, 'update'])->name('admin.booking.update');
});
