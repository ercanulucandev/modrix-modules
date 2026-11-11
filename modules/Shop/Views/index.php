<?php // /shop ve kategoriler. Tema ezebilir: app/Themes/{Tema}/modules/Shop/index.php. Değişkenler: $title, $metaDescription, $products, $categories, $category, $sort, $sorts ?>
<div class="flex flex-wrap items-end justify-between gap-4">
  <div class="max-w-2xl">
    <?php if ($category): ?><a href="<?= Router::url('shop.index') ?>" class="text-sm text-muted-foreground hover:text-foreground"><?= Lang::t('← Tüm ürünler') ?></a><?php endif ?>
    <h1 class="mt-1 text-3xl font-semibold tracking-tight sm:text-4xl"><?= View::e($title) ?></h1>
    <?php if ($metaDescription): ?><p class="mt-2 text-muted-foreground"><?= View::e($metaDescription) ?></p><?php endif ?>
  </div>
  <form><select name="sort" class="input mt-0 w-auto" onchange="this.form.submit()" aria-label="<?= View::e(Lang::t('Sırala')) ?>">
    <?php foreach ($sorts as $k => $label): ?><option value="<?= $k ?>" <?= $sort === $k ? 'selected' : '' ?>><?= $label ?></option><?php endforeach ?>
  </select></form>
</div>

<?php if ($categories): ?>
  <nav class="mt-6 flex flex-wrap gap-2 text-sm" aria-label="<?= View::e(Lang::t('Kategoriler')) ?>">
    <a href="<?= Router::url('shop.index') ?>" class="rounded-full border px-3 py-1 font-medium <?= !$category ? 'border-primary bg-primary text-primary-foreground' : 'hover:bg-muted' ?>"><?= Lang::t('Tümü') ?></a>
    <?php foreach ($categories as $c): ?>
      <a href="<?= Router::url('shop.category', ['slug' => $c['slug']]) ?>" class="rounded-full border px-3 py-1 font-medium <?= ($category['id'] ?? 0) === $c['id'] ? 'border-primary bg-primary text-primary-foreground' : 'hover:bg-muted' ?>"><?= View::e($c['name']) ?></a>
    <?php endforeach ?>
  </nav>
<?php endif ?>

<?php if (!$products['data']): ?>
  <p class="mt-10 rounded-lg border border-dashed px-6 py-16 text-center text-muted-foreground"><?= Lang::t('Bu bölümde henüz ürün yok.') ?></p>
<?php else: ?>
  <div class="mt-8 grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
    <?php foreach ($products['data'] as $p): ?><?= View::render('Shop::product-card', ['p' => $p]) ?><?php endforeach ?>
  </div>
  <div class="mt-10"><?= View::render('partials/pagination', ['page' => $products]) ?></div>
<?php endif ?>
