<?php
// Formlar modülü
//   Site:      POST /forms/{slug}                        gönderim (CSRF + spam koruması)
//   Admin:     /admin/forms, /admin/forms/{id}/edit, /admin/forms/{id}/entries
//   Admin API: PUT /admin/api/forms/{id}                 form oluşturucunun kaydı
use App\Modules\Forms\Controllers\Admin\FormApiController;
use App\Modules\Forms\Controllers\Admin\FormController;
use App\Modules\Forms\Controllers\SubmitController;

Router::post('/forms/{slug}', [SubmitController::class, 'store'])->name('forms.submit');

Router::group(['prefix' => '/admin/forms', 'middleware' => ['admin', 'can:forms.manage']], function () {
    Router::get('/', [FormController::class, 'index'])->name('admin.forms.index');
    Router::post('/create', [FormController::class, 'create'])->name('admin.forms.create');
    Router::get('/{id}/edit', [FormController::class, 'edit'])->name('admin.forms.edit');
    Router::post('/{id}/delete', [FormController::class, 'destroy'])->name('admin.forms.delete');
    Router::get('/{id}/entries', [FormController::class, 'entries'])->name('admin.forms.entries');
    Router::get('/{id}/export', [FormController::class, 'export'])->name('admin.forms.export');
    Router::post('/{id}/entries/read-all', [FormController::class, 'markAllRead'])->name('admin.forms.readAll');
    Router::post('/{id}/entries/{entryId}/read', [FormController::class, 'markRead'])->name('admin.forms.read');
    Router::post('/{id}/entries/{entryId}/delete', [FormController::class, 'deleteEntry'])->name('admin.forms.entryDelete');
});

Router::put('/admin/api/forms/{id}', [FormApiController::class, 'update'])->middleware('admin', 'can:forms.manage')->name('admin.api.forms.update');
