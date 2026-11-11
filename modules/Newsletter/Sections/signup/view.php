<?php $dark = $s['style'] === 'dark'; ?>
<section id="bulten" class="py-14 sm:py-16">
  <div class="wrap">
    <div class="flex flex-col items-start justify-between gap-6 rounded-2xl p-8 sm:p-10 lg:flex-row lg:items-center <?= $dark ? 'bg-foreground text-background' : 'border bg-card' ?>">
      <div class="max-w-xl">
        <h2 class="text-2xl font-semibold tracking-tight" <?= $edit('title') ?>><?= View::e($s['title']) ?></h2>
        <?php if ($s['text'] !== ''): ?><p class="mt-2 <?= $dark ? 'opacity-75' : 'text-muted-foreground' ?>" <?= $edit('text') ?>><?= View::e($s['text']) ?></p><?php endif ?>
      </div>
      <?= View::render('Newsletter::form', ['button' => $s['button'], 'dark' => $dark]) ?>
    </div>
  </div>
</section>
