<?php // Admin > Ürün ekle/düzenle. Değişkenler: $product, $variants, $categories
$p = $product;
$action = $p ? Router::url('admin.shop.products.update', ['id' => $p['id']]) : Router::url('admin.shop.products.store');
$old = fn(string $k, $default = null) => View::old($k, $p[$k] ?? $default);
?>
<form method="post" action="<?= $action ?>" data-product-form>
  <?= View::csrf() ?>
  <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div class="flex items-center gap-2">
      <a href="<?= Router::url('admin.shop.products') ?>" class="btn btn-ghost size-9 p-0" aria-label="Ürünlere dön"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg></a>
      <h1 class="text-2xl font-semibold tracking-tight"><?= $p ? View::e($p['name']) : 'Yeni ürün' ?></h1>
    </div>
    <div class="flex gap-2">
      <?php if ($p && $p['status'] === 'published'): ?><a href="<?= Router::url('shop.product', ['slug' => $p['slug']]) ?>" target="_blank" class="btn btn-secondary">Sitede gör</a><?php endif ?>
      <button class="btn btn-primary">Kaydet</button>
    </div>
  </div>

  <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
    <div class="min-w-0 space-y-6">
      <section class="card space-y-5 p-6">
        <?= View::render('partials/field', ['name' => 'name', 'label' => 'Ürün adı', 'required' => true, 'value' => $old('name')]) ?>
        <?= View::render('partials/field', ['name' => 'summary', 'label' => 'Kısa açıklama', 'type' => 'textarea', 'rows' => 2, 'value' => $old('summary'), 'help' => 'Fiyatın altında ve aramalarda görünür.']) ?>
        <div>
          <span class="label">Ürün açıklaması</span>
          <div class="mt-2" data-editor></div>
          <textarea name="description" hidden data-description><?= View::e((string) $old('description', '')) ?></textarea>
        </div>
      </section>

      <section class="card p-6">
        <?= View::render('admin/settings/fields', ['fields' => ['images' => ['label' => 'Görseller', 'type' => 'gallery', 'max' => 20, 'help' => 'İlk görsel kapak olur. Sürükleyerek sıralayın.']], 'values' => ['images' => $p['images'] ?? []]]) ?>
      </section>

      <section class="card p-6">
        <div class="flex items-start justify-between gap-4">
          <div><h2 class="font-semibold">Seçenekler (varyant)</h2><p class="mt-1 text-sm text-muted-foreground">Beden, renk gibi seçenekler. Fiyatı boş bırakılan, ürünün fiyatını kullanır; stok boşsa sınırsız.</p></div>
          <button type="button" class="btn btn-secondary btn-sm shrink-0" data-add-variant>Seçenek ekle</button>
        </div>
        <?php if ($e = View::error('variants')): ?><p class="field-error"><?= View::e($e) ?></p><?php endif ?>
        <div class="mt-4 space-y-2" data-variants>
          <?php foreach ($variants as $i => $v): ?>
            <div class="grid grid-cols-[1fr_6rem_6rem_6rem_auto] gap-2" data-variant>
              <input type="hidden" name="variants[<?= $i ?>][id]" value="<?= $v['id'] ?>">
              <input name="variants[<?= $i ?>][name]" value="<?= View::e($v['name']) ?>" placeholder="Kırmızı / M" class="input mt-0" aria-label="Seçenek adı">
              <input name="variants[<?= $i ?>][price]" value="<?= View::e((string) $v['price']) ?>" placeholder="Fiyat" class="input mt-0" inputmode="decimal" aria-label="Fiyat">
              <input name="variants[<?= $i ?>][stock]" value="<?= View::e((string) $v['stock']) ?>" placeholder="Stok" class="input mt-0" inputmode="numeric" aria-label="Stok">
              <input name="variants[<?= $i ?>][sku]" value="<?= View::e((string) $v['sku']) ?>" placeholder="Kod" class="input mt-0" aria-label="Ürün kodu">
              <button type="button" class="btn btn-ghost btn-sm text-destructive" data-remove-variant aria-label="Kaldır">✕</button>
            </div>
          <?php endforeach ?>
        </div>
        <template data-variant-template>
          <div class="grid grid-cols-[1fr_6rem_6rem_6rem_auto] gap-2" data-variant>
            <input name="variants[__i__][name]" placeholder="Kırmızı / M" class="input mt-0" aria-label="Seçenek adı">
            <input name="variants[__i__][price]" placeholder="Fiyat" class="input mt-0" inputmode="decimal" aria-label="Fiyat">
            <input name="variants[__i__][stock]" placeholder="Stok" class="input mt-0" inputmode="numeric" aria-label="Stok">
            <input name="variants[__i__][sku]" placeholder="Kod" class="input mt-0" aria-label="Ürün kodu">
            <button type="button" class="btn btn-ghost btn-sm text-destructive" data-remove-variant aria-label="Kaldır">✕</button>
          </div>
        </template>
      </section>

      <section class="card space-y-5 p-6">
        <h2 class="font-semibold">Arama motorları</h2>
        <?= View::render('partials/field', ['name' => 'slug', 'label' => 'Adres', 'value' => $old('slug'), 'help' => 'Boşsa addan üretilir: /shop/' . ($p['slug'] ?? 'urun-adi')]) ?>
        <?= View::render('partials/field', ['name' => 'meta_description', 'label' => 'Meta açıklama', 'type' => 'textarea', 'rows' => 2, 'value' => $old('meta_description')]) ?>
      </section>
    </div>

    <aside class="space-y-6">
      <section class="card space-y-4 p-5">
        <?= View::render('partials/field', ['name' => 'status', 'label' => 'Durum', 'type' => 'select', 'value' => $old('status', 'published'), 'options' => ['published' => 'Satışta', 'draft' => 'Taslak (gizli)']]) ?>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="featured" value="1" class="size-4" <?= $old('featured') ? 'checked' : '' ?>> Öne çıkan ürün</label>
        <?= View::render('partials/field', ['name' => 'category_id', 'label' => 'Kategori', 'type' => 'select', 'value' => (string) $old('category_id', ''), 'options' => ['' => '—'] + $categories]) ?>
        <a href="<?= Request::url('admin/collections/shop_categories') ?>" class="text-xs text-muted-foreground hover:underline">Kategorileri düzenle</a>
      </section>
      <section class="card space-y-4 p-5">
        <?= View::render('partials/field', ['name' => 'price', 'label' => 'Fiyat (₺)', 'required' => true, 'value' => $old('price'), 'attrs' => 'inputmode="decimal"']) ?>
        <?= View::render('partials/field', ['name' => 'compare_price', 'label' => 'Eski fiyat (₺)', 'value' => $old('compare_price'), 'attrs' => 'inputmode="decimal"', 'help' => 'İndirim göstermek için; üstü çizili görünür.']) ?>
        <?= View::render('partials/field', ['name' => 'stock', 'label' => 'Stok', 'value' => $old('stock'), 'attrs' => 'inputmode="numeric"', 'help' => 'Boş: stok takibi yok. Seçenek varsa seçeneklerin stoğu geçerlidir.']) ?>
        <?= View::render('partials/field', ['name' => 'sku', 'label' => 'Ürün kodu (SKU)', 'value' => $old('sku')]) ?>
      </section>
      <?php if ($p): ?>
        <button form="delete-product" class="btn btn-danger-ghost btn-sm w-full">Ürünü sil</button>
      <?php endif ?>
    </aside>
  </div>
</form>
<?php if ($p): ?><form id="delete-product" method="post" action="<?= Router::url('admin.shop.products.delete', ['id' => $p['id']]) ?>" onsubmit="return confirm('Ürün silinsin mi?')"><?= View::csrf() ?></form><?php endif ?>
<script type="module" src="<?= View::e(View::asset('admin/settings-page.js')) ?>"></script>
<script type="module" src="<?= View::e(Module::asset('Shop', 'admin/product-form.js')) ?>"></script>
