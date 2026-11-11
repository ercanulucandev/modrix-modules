<?php // Ürün sayfası. Değişkenler: $product, $variants, $available, $category, $related
use App\Modules\Shop\Services\Shop;
$p = $product;
[$min, $max] = Shop::priceRange($p, $variants);
$images = array_map(fn($path) => Request::url('media/' . $path), $p['images']);
?>
<nav class="text-sm text-muted-foreground">
  <a href="<?= Router::url('shop.index') ?>" class="hover:text-foreground"><?= View::e(Setting::get('shop_title', 'Mağaza')) ?></a>
  <?php if ($category): ?> / <a href="<?= Router::url('shop.category', ['slug' => $category['slug']]) ?>" class="hover:text-foreground"><?= View::e($category['name']) ?></a><?php endif ?>
</nav>

<div class="mt-4 grid gap-10 lg:grid-cols-2">
  <div>
    <div class="aspect-square overflow-hidden rounded-xl border bg-muted">
      <?php if ($images): ?><img src="<?= View::e($images[0]) ?>"<?= App\Services\ResponsiveImages::attrs($images[0], '(max-width: 1024px) 100vw, 50vw') ?> alt="<?= View::e($p['name']) ?>" class="size-full object-cover" data-main-image><?php endif ?>
    </div>
    <?php if (count($images) > 1): ?>
      <div class="mt-3 grid grid-cols-5 gap-2">
        <?php foreach ($images as $i => $url): ?>
          <button type="button" class="aspect-square overflow-hidden rounded-md border <?= $i === 0 ? 'ring-2 ring-primary' : '' ?>" data-thumb="<?= View::e($url) ?>"><img src="<?= View::e($url) ?>" alt="" loading="lazy" class="size-full object-cover"></button>
        <?php endforeach ?>
      </div>
    <?php endif ?>
  </div>

  <div>
    <h1 class="text-3xl font-semibold tracking-tight"><?= View::e($p['name']) ?></h1>
    <p class="mt-3 flex flex-wrap items-baseline gap-3">
      <span class="text-2xl font-semibold" data-price><?= $min === $max ? Shop::money($min) : Shop::money($min) . ' – ' . Shop::money($max) ?></span>
      <?php if ($p['compare_price'] && (float) $p['compare_price'] > $min): ?><span class="text-lg text-muted-foreground line-through"><?= Shop::money($p['compare_price']) ?></span><?php endif ?>
    </p>
    <?php if ($p['summary']): ?><p class="mt-4 leading-7 text-muted-foreground"><?= View::e($p['summary']) ?></p><?php endif ?>

    <form method="post" action="<?= Router::url('shop.cart.add') ?>" class="mt-6 space-y-4" data-product-form>
      <?= View::csrf() ?>
      <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
      <?php if ($variants): ?>
        <div>
          <label for="variant" class="label"><?= Lang::t('Seçenek') ?></label>
          <select id="variant" name="variant_id" required class="input mt-2">
            <option value=""><?= Lang::t('Seçin') ?></option>
            <?php foreach ($variants as $v): $out = $v['stock'] !== null && (int) $v['stock'] <= 0; ?>
              <option value="<?= $v['id'] ?>" data-price="<?= View::e(Shop::money($v['price'] ?? $p['price'])) ?>" <?= $out ? 'disabled' : '' ?>><?= View::e($v['name']) ?><?= $out ? ' (tükendi)' : '' ?></option>
            <?php endforeach ?>
          </select>
        </div>
      <?php endif ?>
      <div class="flex gap-3">
        <input type="number" name="quantity" value="1" min="1" max="<?= App\Modules\Shop\Services\Cart::MAX_QTY ?>" class="input mt-0 h-11 w-20" aria-label="<?= View::e(Lang::t('Adet')) ?>">
        <button class="btn btn-primary h-11 flex-1" <?= $available ? '' : 'disabled' ?>><?= $available ? 'Sepete ekle' : 'Tükendi' ?></button>
      </div>
      <?php if (!$variants && $p['stock'] !== null && (int) $p['stock'] > 0 && (int) $p['stock'] <= 5): ?><p class="text-sm text-amber-600">Son <?= (int) $p['stock'] ?> ürün!</p><?php endif ?>
    </form>
    <?php if ($p['sku']): ?><p class="mt-6 text-xs text-muted-foreground">Ürün kodu: <?= View::e($p['sku']) ?></p><?php endif ?>
  </div>
</div>

<?php if (trim(strip_tags((string) $p['description'])) !== ''): ?>
  <section class="mt-14 max-w-3xl">
    <h2 class="text-xl font-semibold tracking-tight"><?= Lang::t('Ürün açıklaması') ?></h2>
    <div class="prose-content mt-4"><?= Hook::filter('content.html', Html::render($p['description']), $p) ?></div>
  </section>
<?php endif ?>

<?php if ($related): ?>
  <section class="mt-16">
    <h2 class="text-xl font-semibold tracking-tight"><?= Lang::t('Benzer ürünler') ?></h2>
    <div class="mt-5 grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4"><?php foreach ($related as $r): ?><?= View::render('Shop::product-card', ['p' => $r]) ?><?php endforeach ?></div>
  </section>
<?php endif ?>

<script>
  document.querySelectorAll('[data-thumb]').forEach((b) => b.addEventListener('click', () => {
    document.querySelector('[data-main-image]').src = b.dataset.thumb;
    document.querySelectorAll('[data-thumb]').forEach((x) => x.classList.toggle('ring-2', x === b));
    document.querySelectorAll('[data-thumb]').forEach((x) => x.classList.toggle('ring-primary', x === b));
  }));
  document.querySelector('#variant')?.addEventListener('change', (e) => {
    const o = e.target.selectedOptions[0];
    if (o?.dataset.price) document.querySelector('[data-price]').textContent = o.dataset.price;
  });
</script>
