<?php
namespace App\Modules\Portfolio\Controllers;

use App\Services\Collection;
use Core\Controller;
use Core\Seo;
use Core\Setting;
use Core\View;

class PortfolioController extends Controller
{
    public function index(): View
    {
        $items = Collection::items('portfolio');
        return $this->view('Portfolio::index', [
            'title'           => Setting::get('portfolio_title', 'Projelerimiz'),
            'metaDescription' => Setting::get('portfolio_description'),
            'items'           => $items,
            'categories'      => array_values(array_unique(array_filter(array_map(fn($p) => trim((string) $p['category']), $items)))),
        ]);
    }

    public function show(string $slug): View
    {
        $p = Collection::findBySlug('portfolio', $slug) ?? $this->abort(404, 'Proje bulunamadı');
        $cover = self::cover($p);
        Seo::add(array_filter(['@type' => 'CreativeWork', 'name' => $p['title'], 'description' => $p['summary'] ?: null,
            'image' => $cover ? Seo::absolute($cover) : null, 'dateCreated' => $p['year'] ?: null]));

        $others = array_slice(array_values(array_filter(Collection::items('portfolio'), fn($o) => $o['id'] !== $p['id'])), 0, 3);
        return $this->view('Portfolio::show', [
            'title'           => $p['title'],
            'metaDescription' => $p['summary'] ?: mb_strimwidth((string) $p['description'], 0, 160, '…'),
            'metaImage'       => $cover,
            'project'         => $p,
            'others'          => $others,
        ]);
    }

    public static function cover(array $p): ?string
    {
        return Collection::imageUrl(($p['cover'] ?? '') ?: ($p['images'][0] ?? null));
    }
}
