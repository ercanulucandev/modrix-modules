<?php
$items = array_slice(App\Services\Collection::items('portfolio'), 0, (int) $s['count']);
if (!$items) return;
?>
<section class="py-14 sm:py-16">
  <div class="wrap">
    <div class="flex flex-wrap items-end justify-between gap-4">
      <div>
        <h2 class="text-2xl font-semibold tracking-tight sm:text-3xl" <?= $edit('title') ?>><?= View::e($s['title']) ?></h2>
        <?php if ($s['text'] !== ''): ?><p class="mt-2 text-muted-foreground" <?= $edit('text') ?>><?= View::e($s['text']) ?></p><?php endif ?>
      </div>
      <?php if ($s['link_text'] !== ''): ?><a href="<?= Router::url('portfolio.index') ?>" class="inline-flex items-center gap-1.5 text-sm font-medium hover:underline" <?= $edit('link_text') ?>><?= View::e($s['link_text']) ?> <?= Icon::svg('arrow', 'size-4') ?></a><?php endif ?>
    </div>
    <div class="mt-8"><?= View::render('Portfolio::grid', ['items' => $items]) ?></div>
  </div>
</section>
