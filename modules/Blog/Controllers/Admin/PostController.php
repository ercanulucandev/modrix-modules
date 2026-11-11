<?php
namespace App\Modules\Blog\Controllers\Admin;

use App\Controllers\Admin\AdminController;
use App\Modules\Blog\Services\PostService;
use App\Services\Translations;
use Core\Lang;
use Core\Request;
use Core\View;

// Admin > Yazılar: liste ve düzenleyici sayfa iskeletleri. İçerik ve kayıt API ile (PostApiController).
class PostController extends AdminController
{
    public function index(): View
    {
        if ($lang = Request::get('lang')) Lang::setContent((string) $lang);
        return $this->view('Blog::admin/index', ['title' => 'Yazılar']);
    }

    public function create(): View
    {
        return $this->view('Blog::admin/edit', ['title' => 'Yeni yazı', 'post' => null, 'lang' => Lang::content(), 'siblings' => []]);
    }

    public function edit(string $id): View
    {
        $post = PostService::find((int) $id);
        Lang::setContent($post['lang']);
        return $this->view('Blog::admin/edit', ['title' => 'Yazıyı düzenle', 'post' => $post, 'lang' => $post['lang'],
            'siblings' => Translations::siblings('blog_posts', $post, ['id', 'lang', 'status'])]);
    }

    // Başka dile çeviri: taslak kopya oluşturup düzenleyicisini açar
    public function translate(string $id, string $lang): void
    {
        if (!Lang::exists($lang)) $this->abort(404);
        $copy = PostService::translate((int) $id, $lang, $this->admin()['id']);
        Lang::setContent($lang);
        $this->redirect("admin/blog/{$copy['id']}/edit", Lang::name($lang) . ' çevirisi taslak olarak oluşturuldu; metni çevirip yayınlayın.');
    }
}
