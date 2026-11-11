<?php
use App\Modules\Shop\Services\Shop;
$q = Shop::products();
if ($s['source'] === 'featured') $q->where('featured', 1);
$items = array_map([Shop::class, 'decode'], $q->orderByDesc('id')->limit((int) $s['count'])->get());
if (!$items && $s['source'] === 'featured') $items = array_map([Shop::class, 'decode'], Shop::products()->orderByDesc('id')->limit((int) $s['count'])->get());
if (!$items) return;
?>
<section class="py-14 sm:py-16">
  <div class="wrap">
    <div class="flex flex-wrap items-end justify-between gap-4">
      <div>
        <h2 class="text-2xl font-semibold tracking-tight sm:text-3xl" <?= $edit('title') ?>><?= View::e($s['title']) ?></h2>
        <?php if ($s['text'] !== ''): ?><p class="mt-2 text-muted-foreground" <?= $edit('text') ?>><?= View::e($s['text']) ?></p><?php endif ?>
      </div>
      <?php if ($s['link_text'] !== ''): ?><a href="<?= Router::url('shop.index') ?>" class="inline-flex items-center gap-1.5 text-sm font-medium hover:underline" <?= $edit('link_text') ?>><?= View::e($s['link_text']) ?> <?= Icon::svg('arrow', 'size-4') ?></a><?php endif ?>
    </div>
    <div class="mt-8 grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4"><?php foreach ($items as $p): ?><?= View::render('Shop::product-card', ['p' => $p]) ?><?php endforeach ?></div>
  </div>
</section>
