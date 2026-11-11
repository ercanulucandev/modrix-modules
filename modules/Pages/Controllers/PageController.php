<?php
namespace App\Modules\Pages\Controllers;

use App\Models\User;
use App\Modules\Pages\Models\Page;
use App\Modules\Pages\Services\PageService;
use Core\Controller;
use Core\Html;
use Core\Request;
use Core\Response;
use Core\Setting;
use Core\View;

// Sitede sayfa gösterimi. Adres route'la değil Router::fallback ile yakalanır (routes.php):
// hiçbir route eşleşmeyince /hakkimizda, /kurumsal/ekibimiz gibi adresler sayfalarda aranır.
//
// Görünüm: şablon dosyası Pages::templates/{şablon} (tema ezebilir: app/Themes/{Tema}/modules/Pages/templates/...),
// 'default' şablonu Pages::show. Taslaklar sadece giriş yapmış admin'e gösterilir.
class PageController extends Controller
{
    // Router::fallback: sayfa yoksa null (-> 404)
    public static function resolve(string $path): ?View
    {
        $page = Page::findByPath($path);
        if (!$page && !\Core\Lang::isDefault()) {
            // Bu dilde henüz çevrilmemiş: varsayılan dildeki sayfaya
            $fallback = Page::findByPath($path, \Core\Lang::default());
            if ($fallback && $fallback['status'] === 'published') {
                header('Location: ' . \Core\Lang::url($fallback['path'], \Core\Lang::default()), true, 302);
                exit;
            }
        }
        if (!$page) return null;
        if ($page['status'] !== 'published' && !User::admin()) return null;

        // Ana sayfa olarak seçilmiş sayfanın kendi adresi ana sayfaya yönlenir (aynı içerik iki adreste olmasın)
        $front = self::frontPage();
        if ($front && (int) $front['id'] === (int) $page['id']) Response::redirect('/');

        return (new self)->render($page);
    }

    // Ana sayfa olarak seçilen sayfa geçerli dilde (seçilen sayfanın o dildeki çevirisi; yoksa null: temanın ana sayfası)
    public static function frontPage(): ?array
    {
        $id = (int) Setting::get('pages_front_page');
        $page = $id ? Page::find($id) : null;
        if (!$page) return null;
        if ($page['lang'] !== \Core\Lang::current()) $page = \App\Services\Translations::siblings('pages', $page)[\Core\Lang::current()] ?? null;
        return $page && $page['status'] === 'published' ? $page : null;
    }

    public function render(array $page, bool $isFront = false): View
    {
        // Dil seçici / hreflang: bu sayfanın diğer dillerdeki adresi (çevirisi yoksa o dilin ana sayfası)
        if (\Core\Lang::enabled()) {
            $front = (int) Setting::get('pages_front_page');
            $map = [];
            $siblings = \App\Services\Translations::siblings('pages', $page);
            foreach (\Core\Lang::all() as $code => $_) {
                $s = $siblings[$code] ?? null;
                $map[$code] = $s && $s['status'] === 'published' && !$isFront ? $s['path'] : '';
            }
            \Core\Lang::setAlternates($map);
        }
        $template = $page['template'] === 'default' ? 'show' : 'templates/' . $page['template'];
        if ($template !== 'show' && !is_file(View::path("Pages::$template"))) $template = 'show';

        return $this->view("Pages::$template", [
            'title'           => $isFront ? null : $page['title'],
            'metaDescription' => $page['meta_description'] ?: Html::text($page['body'], 160),
            'metaImage'       => $page['image'] ? Request::url('media/' . $page['image']) : null,
            'page'            => $page,
            'isFront'         => $isFront,
            'isDraft'         => $page['status'] !== 'published',
            'ancestors'       => $isFront ? [] : Page::ancestors($page),
            'children'        => Setting::bool('pages_show_children') ? Page::publishedChildren((int) $page['id']) : [],
            'templates'       => PageService::templates(),
        ]);
    }
}
