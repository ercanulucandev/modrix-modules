<?php
namespace App\Modules\Pages\Controllers\Admin;

use App\Controllers\Admin\AdminController;
use App\Modules\Pages\Models\Page;
use App\Modules\Pages\Services\PageService;
use Core\View;

// Admin > Sayfalar: liste ve düzenleyici sayfa iskeletleri. İçerik ve kayıt API ile (PageApiController).
class PageController extends AdminController
{
    public function index(): View
    {
        if ($lang = Request::get('lang')) \Core\Lang::setContent((string) $lang);
        return $this->view('Pages::admin/index', ['title' => 'Sayfalar']);
    }

    // Başka dile çeviri: taslak kopya oluşturup düzenleyicisini açar
    public function translate(string $id, string $lang): void
    {
        if (!\Core\Lang::exists($lang)) $this->abort(404);
        $copy = PageService::translate((int) $id, $lang, $this->admin()['id']);
        \Core\Lang::setContent($lang);
        $this->redirect("admin/pages/{$copy['id']}/edit", \Core\Lang::name($lang) . ' çevirisi taslak olarak oluşturuldu; metni çevirip yayınlayın.');
    }

    public function create(): View
    {
        return $this->editor(null);
    }

    public function edit(string $id): View
    {
        $page = PageService::find((int) $id);
        \Core\Lang::setContent($page['lang']);   // üst sayfa listesi bu dilden gelsin
        return $this->editor($page);
    }

    private function editor(?array $page): View
    {
        return $this->view('Pages::admin/edit', [
            'title' => $page ? 'Sayfayı düzenle' : 'Yeni sayfa',
            'page'  => $page,
            'lang'  => $page['lang'] ?? \Core\Lang::content(),
            'siblings' => $page ? \App\Services\Translations::siblings('pages', $page, ['id', 'lang', 'status']) : [],
        ]);
    }
}
