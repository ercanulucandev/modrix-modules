<?php
use App\Modules\Faq\Controllers\FaqController;
$items = array_slice(array_values(array_filter(FaqController::items(), fn($i) => $s['group'] === '' || mb_strtolower(trim((string) $i['group'])) === mb_strtolower(trim($s['group'])))), 0, (int) $s['count']);
if (!$items) return;
?>
<section class="py-14 sm:py-16">
  <div class="wrap grid gap-10 lg:grid-cols-[1fr_2fr]">
    <div>
      <h2 class="text-2xl font-semibold tracking-tight sm:text-3xl" <?= $edit('title') ?>><?= View::e($s['title']) ?></h2>
      <?php if ($s['text'] !== ''): ?><p class="mt-3 text-muted-foreground" <?= $edit('text') ?>><?= View::e($s['text']) ?></p><?php endif ?>
      <?php if ($s['link_text'] !== ''): ?><a href="<?= Router::url('faq.index') ?>" class="mt-5 inline-flex items-center gap-1.5 text-sm font-medium hover:underline" <?= $edit('link_text') ?>><?= View::e($s['link_text']) ?> <?= Icon::svg('arrow', 'size-4') ?></a><?php endif ?>
    </div>
    <?= View::render('Faq::list', ['items' => $items]) ?>
  </div>
</section>
