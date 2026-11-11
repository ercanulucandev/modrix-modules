<?php
namespace App\Modules\Forms\Controllers\Admin;

use App\Controllers\Admin\AdminController;
use App\Modules\Forms\Models\Form;
use App\Modules\Forms\Services\FormService;

// Form oluşturucunun kaydı: PUT /admin/api/forms/{id}  { name, slug, fields: [...], settings: {...} }
class FormApiController extends AdminController
{
    public function update(string $id): array
    {
        $form = Form::get((int) $id) ?? $this->abort(404);
        $name = trim((string) Request::post('name', ''));
        if ($name === '' || mb_strlen($name) > 100) \Core\Validator::fail(['name' => 'Form adı 1-100 karakter olmalı.']);

        Form::update($form['id'], [
            'name'     => $name,
            'slug'     => FormService::uniqueSlug((string) (Request::post('slug') ?: $name), $form['id']),
            'fields'   => json_encode(FormService::cleanFields(Request::post('fields')), JSON_UNESCAPED_UNICODE),
            'settings' => json_encode(FormService::cleanSettings(Request::post('settings')), JSON_UNESCAPED_UNICODE),
        ]);
        Log::info('Form kaydedildi', ['form' => $form['id'], 'admin' => $this->admin()['id']]);
        \App\Services\Activity::log('form.updated', '"' . $name . '" formunu kaydetti', 'admin/forms/' . $form['id'] . '/edit');
        return ['data' => Form::get($form['id']), 'message' => 'Form kaydedildi.'];
    }
}
