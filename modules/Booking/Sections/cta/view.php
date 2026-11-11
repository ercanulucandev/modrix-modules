<section class="py-14 sm:py-16">
  <div class="wrap">
    <div class="rounded-2xl border bg-card p-8 sm:p-10">
      <div class="flex flex-wrap items-center justify-between gap-6">
        <div class="max-w-xl">
          <h2 class="text-2xl font-semibold tracking-tight sm:text-3xl" <?= $edit('title') ?>><?= View::e($s['title']) ?></h2>
          <?php if ($s['text'] !== ''): ?><p class="mt-2 text-muted-foreground" <?= $edit('text') ?>><?= View::e($s['text']) ?></p><?php endif ?>
        </div>
        <?php if ($s['style'] === 'button'): ?><a href="<?= Router::url('booking.index') ?>" class="btn btn-primary h-11 px-6" <?= $edit('button') ?>><?= View::e($s['button']) ?></a><?php endif ?>
      </div>
      <?php if ($s['style'] === 'form'): ?><div class="mt-8"><?= View::render('Booking::widget') ?></div><?php endif ?>
    </div>
  </div>
</section>
