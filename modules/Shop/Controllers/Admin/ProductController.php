<?php
namespace App\Modules\Shop\Controllers\Admin;

use App\Controllers\Admin\AdminController;
use App\Models\Media;
use App\Modules\Shop\Services\Shop;
use App\Services\Activity;
use App\Services\Collection;
use Core\Database;
use Core\Hook;
use Core\Html;
use Core\Request;
use Core\Validator;
use Core\View;

// Admin > Ürünler
class ProductController extends AdminController
{
    public function index(): View
    {
        $q = trim((string) Request::get('q', ''));
        $category = (int) Request::get('category', 0);
        $status = (string) Request::get('status', '');
        $rows = Database::table('shop_products')
            ->when($category, fn($query) => $query->where('category_id', $category))
            ->when(in_array($status, ['published', 'draft'], true), fn($query) => $query->where('status', $status))
            ->when($status === 'out', fn($query) => $query->whereRaw('stock IS NOT NULL AND stock <= 0'))
            ->search(['name', 'sku'], $q)->orderByDesc('id')->paginate(30);
        $rows['data'] = array_map([Shop::class, 'decode'], $rows['data']);
        return $this->view('Shop::admin/products', [
            'title' => 'Ürünler', 'rows' => $rows, 'categories' => array_column(Collection::items('shop_categories', null, true), 'name', 'id'),
            'filter' => ['q' => $q, 'category' => $category, 'status' => $status],
        ]);
    }

    public function create(): View
    {
        return $this->form(null);
    }

    public function edit(string $id): View
    {
        return $this->form(Shop::product((int) $id) ?? $this->abort(404));
    }

    public function store(): void
    {
        $id = $this->save(null);
        $this->redirect("admin/shop/products/$id/edit", 'Ürün eklendi.');
    }

    public function update(string $id): void
    {
        $product = Shop::product((int) $id) ?? $this->abort(404);
        $this->save($product);
        $this->redirect("admin/shop/products/{$product['id']}/edit", 'Kaydedildi.');
    }

    public function destroy(string $id): void
    {
        $product = Shop::product((int) $id) ?? $this->abort(404);
        Database::transaction(function () use ($product) {
            Database::table('shop_variants')->where('product_id', $product['id'])->delete();
            Database::table('shop_products')->where('id', $product['id'])->delete();
        });
        Activity::log('shop.product', "Ürünü sildi: {$product['name']}");
        $this->redirect('admin/shop/products', 'Ürün silindi. Geçmiş siparişlerdeki kayıtlar korunur.');
    }

    private function form(?array $product): View
    {
        return $this->view('Shop::admin/product-form', [
            'title'      => $product ? $product['name'] : 'Yeni ürün',
            'product'    => $product,
            'variants'   => $product ? Shop::variants($product['id']) : [],
            'categories' => array_column(Collection::items('shop_categories', null, true), 'name', 'id'),
        ]);
    }

    private function save(?array $product): int
    {
        $data = $this->validate([
            'name'             => 'required|max:200',
            'slug'             => 'nullable|max:180',
            'sku'              => 'nullable|max:64',
            'summary'          => 'nullable|max:500',
            'price'            => 'required|numeric|min:0',
            'compare_price'    => 'nullable|numeric|min:0',
            'stock'            => 'nullable|integer|min:0',
            'category_id'      => 'nullable|integer',
            'status'           => 'required|in:published,draft',
            'meta_description' => 'nullable|max:300',
        ], ['name' => 'Ürün adı', 'price' => 'Fiyat', 'compare_price' => 'Eski fiyat', 'stock' => 'Stok']);

        $images = json_decode((string) Request::post('images', '[]'), true);
        $images = array_values(array_filter(is_array($images) ? $images : [], fn($p) => is_string($p) && ($m = Media::findByPath($p)) && $m['type'] === 'image'));
        $variants = $this->variantInput();

        $values = [
            'name'             => $data['name'],
            'slug'             => $this->uniqueSlug($data['slug'] ?: $data['name'], $product['id'] ?? null),
            'sku'              => $data['sku'] ?: null,
            'summary'          => $data['summary'] ?: null,
            'description'      => Html::sanitize((string) Request::post('description', '')),
            'price'            => number_format((float) $data['price'], 2, '.', ''),
            'compare_price'    => ($data['compare_price'] ?? '') !== '' && $data['compare_price'] !== null ? number_format((float) $data['compare_price'], 2, '.', '') : null,
            'stock'            => ($data['stock'] ?? '') === '' || $data['stock'] === null ? null : (int) $data['stock'],
            'category_id'      => $data['category_id'] ? (int) $data['category_id'] : null,
            'status'           => $data['status'],
            'featured'         => (int) (bool) Request::post('featured'),
            'images'           => json_encode($images, JSON_UNESCAPED_SLASHES),
            'meta_description' => $data['meta_description'] ?: null,
            'updated_at'       => date('Y-m-d H:i:s'),
        ];

        $id = Database::transaction(function () use ($product, $values, $variants) {
            if ($product) {
                Database::table('shop_products')->where('id', $product['id'])->update($values);
                $id = $product['id'];
            } else {
                $id = Database::table('shop_products')->insertGetId($values + ['created_at' => date('Y-m-d H:i:s')]);
            }
            // Varyantlar: gelen listede olmayanlar silinir, olanlar güncellenir (id korunur: sepetteki seçimler bozulmasın)
            $keep = array_filter(array_column($variants, 'id'));
            Database::table('shop_variants')->where('product_id', $id)->when($keep, fn($q) => $q->whereNotIn('id', $keep))->delete();
            foreach ($variants as $i => $v) {
                $row = ['name' => $v['name'], 'sku' => $v['sku'], 'price' => $v['price'], 'stock' => $v['stock'], 'sort_order' => $i];
                if ($v['id']) Database::table('shop_variants')->where('id', $v['id'])->where('product_id', $id)->update($row);
                else Database::table('shop_variants')->insert($row + ['product_id' => $id]);
            }
            return $id;
        });

        if ($product && $product['slug'] !== $values['slug'] && $product['status'] === 'published') Hook::fire('url.moved', 'shop/' . $product['slug'], 'shop/' . $values['slug']);
        Activity::log('shop.product', ($product ? 'Ürünü güncelledi: ' : 'Ürün ekledi: ') . $values['name'], "admin/shop/products/$id/edit");
        return (int) $id;
    }

    // variants[i][id|name|sku|price|stock]
    private function variantInput(): array
    {
        $out = [];
        $errors = [];
        foreach ((array) Request::post('variants', []) as $i => $v) {
            $name = trim((string) ($v['name'] ?? ''));
            if ($name === '') continue;
            $price = trim((string) ($v['price'] ?? ''));
            $stock = trim((string) ($v['stock'] ?? ''));
            if ($price !== '' && !is_numeric($price)) $errors['variants'] = "\"$name\" fiyatı sayı olmalı.";
            if ($stock !== '' && !ctype_digit($stock)) $errors['variants'] = "\"$name\" stoğu tam sayı olmalı.";
            $out[] = ['id' => (int) ($v['id'] ?? 0) ?: null, 'name' => mb_substr($name, 0, 100), 'sku' => mb_substr(trim((string) ($v['sku'] ?? '')), 0, 64) ?: null,
                      'price' => $price === '' ? null : number_format((float) $price, 2, '.', ''), 'stock' => $stock === '' ? null : (int) $stock];
        }
        if ($errors) Validator::fail($errors);
        return array_slice($out, 0, 100);
    }

    private function uniqueSlug(string $text, ?int $exceptId): string
    {
        $base = Shop::slugify($text) ?: 'urun';
        $slug = $base;
        for ($i = 2; ($row = Database::table('shop_products')->where('slug', $slug)->first()) && (int) $row['id'] !== $exceptId; $i++) $slug = "$base-$i";
        return $slug;
    }
}
