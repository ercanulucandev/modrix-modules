<?php // Ürün kartı. Değişken: $p (Shop::decode edilmiş)
use App\Modules\Shop\Services\Shop;
$variants = Shop::variants($p['id']);
[$min, $max] = Shop::priceRange($p, $variants);
$img = Shop::image($p);
$available = Shop::available($p, $variants);
?>
<a href="<?= Router::url('shop.product', ['slug' => $p['slug']]) ?>" class="card group block overflow-hidden transition hover:-translate-y-0.5 hover:shadow-lg">
  <div class="relative aspect-square overflow-hidden bg-muted">
    <?php if ($img): ?><img src="<?= View::e($img) ?>"<?= App\Services\ResponsiveImages::attrs($img, '(max-width: 640px) 50vw, 25vw') ?> alt="<?= View::e($p['name']) ?>" loading="lazy" class="size-full object-cover transition duration-500 group-hover:scale-105"><?php endif ?>
    <?php if (!$available): ?><span class="absolute left-2 top-2 badge badge-gray"><?= Lang::t('Tükendi') ?></span>
    <?php elseif ($p['compare_price'] && (float) $p['compare_price'] > $min): ?><span class="absolute left-2 top-2 rounded bg-red-600 px-1.5 py-0.5 text-xs font-semibold text-white">%<?= (int) round(100 - $min * 100 / (float) $p['compare_price']) ?></span><?php endif ?>
  </div>
  <div class="p-4">
    <h3 class="line-clamp-2 font-medium"><?= View::e($p['name']) ?></h3>
    <p class="mt-1.5 flex flex-wrap items-baseline gap-x-2">
      <span class="font-semibold"><?= $min === $max ? Shop::money($min) : Shop::money($min) . ' – ' . Shop::money($max) ?></span>
      <?php if ($p['compare_price'] && (float) $p['compare_price'] > $min): ?><span class="text-sm text-muted-foreground line-through"><?= Shop::money($p['compare_price']) ?></span><?php endif ?>
    </p>
  </div>
</a>
