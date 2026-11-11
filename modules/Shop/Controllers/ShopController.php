<?php
namespace App\Modules\Shop\Controllers;

use App\Modules\Shop\Services\Shop;
use App\Services\Collection;
use Core\Controller;
use Core\Html;
use Core\Request;
use Core\Seo;
use Core\Setting;
use Core\View;

class ShopController extends Controller
{
    private const SORTS = ['new' => 'En yeni', 'price_asc' => 'Fiyat: düşükten yükseğe', 'price_desc' => 'Fiyat: yüksekten düşüğe', 'name' => 'Ada göre'];

    public function index(): View
    {
        return $this->listing(null, Setting::get('shop_title', 'Mağaza'), null);
    }

    public function category(string $slug): View
    {
        $category = Collection::findBySlug('shop_categories', $slug) ?? $this->abort(404, 'Kategori bulunamadı');
        return $this->listing($category, $category['name'], $category['description'] ?: null);
    }

    public function show(string $slug): View
    {
        $p = Shop::productBySlug($slug) ?? $this->abort(404, 'Ürün bulunamadı');
        $variants = Shop::variants($p['id']);
        [$min, $max] = Shop::priceRange($p, $variants);
        $available = Shop::available($p, $variants);
        $image = Shop::image($p);
        $description = $p['meta_description'] ?: ($p['summary'] ?: Html::text($p['description'], 160));

        Seo::add(array_filter([
            '@type' => 'Product', 'name' => $p['name'], 'description' => $description, 'sku' => $p['sku'] ?: null,
            'image' => array_map(fn($path) => Seo::absolute(Request::url('media/' . $path)), $p['images']) ?: null,
            'offers' => ['@type' => $min === $max ? 'Offer' : 'AggregateOffer', 'priceCurrency' => 'TRY',
                         ...($min === $max ? ['price' => number_format($min, 2, '.', '')] : ['lowPrice' => number_format($min, 2, '.', ''), 'highPrice' => number_format($max, 2, '.', '')]),
                         'availability' => 'https://schema.org/' . ($available ? 'InStock' : 'OutOfStock'),
                         'url' => Seo::absolute(Request::url('shop/' . $p['slug']))],
        ]));

        return $this->view('Shop::show', [
            'title'           => $p['name'],
            'metaDescription' => $description,
            'metaImage'       => $image,
            'product'         => $p,
            'variants'        => $variants,
            'available'       => $available,
            'category'        => $p['category_id'] ? Collection::find('shop_categories', (int) $p['category_id']) : null,
            'related'         => array_map([Shop::class, 'decode'], Shop::products()->where('id', '!=', $p['id'])
                                    ->when($p['category_id'], fn($q) => $q->where('category_id', $p['category_id']))->orderByDesc('featured')->orderByDesc('id')->limit(4)->get()),
        ]);
    }

    private function listing(?array $category, string $title, ?string $description): View
    {
        $sort = array_key_exists($s = (string) Request::get('sort', 'new'), self::SORTS) ? $s : 'new';
        $query = Shop::products()->when($category, fn($q) => $q->where('category_id', $category['id']));
        $query = match ($sort) {
            'price_asc'  => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'name'       => $query->orderBy('name'),
            default      => $query->orderByDesc('featured')->orderBy('sort_order')->orderByDesc('id'),
        };
        $page = $query->paginate(max(4, min(96, (int) Setting::get('shop_per_page', 12))));
        $page['data'] = array_map([Shop::class, 'decode'], $page['data']);

        return $this->view('Shop::index', [
            'title'           => $title,
            'metaDescription' => $description,
            'products'        => $page,
            'categories'      => Collection::items('shop_categories'),
            'category'        => $category,
            'sort'            => $sort,
            'sorts'           => self::SORTS,
        ]);
    }
}
