<?php
namespace App\Modules\Forms\Controllers;

use App\Modules\Forms\Models\Form;
use App\Modules\Forms\Services\FormService;
use Core\Controller;
use Core\Request;
use Core\Session;

// Sitedeki formların gönderimi: POST /forms/{slug}
// JS açıkken (Assets/form.js) JSON döner ve sayfa yenilenmeden teşekkür mesajı gösterilir;
// JS kapalıysa geldiği sayfaya mesajla geri döner.
class SubmitController extends Controller
{
    public function store(string $slug)
    {
        $form = Form::findBySlug($slug) ?? $this->abort(404, 'Form bulunamadı');
        $message = FormService::submit($form);   // hatada 422 (alan hataları) / 429 (çok fazla deneme)

        if (Request::isAjax()) return ['message' => $message];

        Session::flash('form_success_' . $form['slug'], $message);
        $back = (string) ($_SERVER['HTTP_REFERER'] ?? '');
        header('Location: ' . (str_starts_with($back, 'http') ? $back . '#form-' . $form['slug'] : Request::url('/')));
        exit;
    }
}
