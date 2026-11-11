<?php
$items = array_slice(App\Services\Collection::items('team'), 0, (int) $s['count']);
if (!$items) return;
?>
<section class="py-14 sm:py-16">
  <div class="wrap">
    <div class="mx-auto max-w-2xl text-center">
      <h2 class="text-2xl font-semibold tracking-tight sm:text-3xl" <?= $edit('title') ?>><?= View::e($s['title']) ?></h2>
      <?php if ($s['text'] !== ''): ?><p class="mt-3 text-muted-foreground" <?= $edit('text') ?>><?= View::e($s['text']) ?></p><?php endif ?>
    </div>
    <div class="mt-10"><?= View::render('Team::grid', ['items' => $items, 'columns' => (int) $s['columns']]) ?></div>
  </div>
</section>
