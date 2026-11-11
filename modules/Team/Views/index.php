<?php // /team. Tema ezebilir: app/Themes/{Tema}/modules/Team/index.php. Değişkenler: $title, $metaDescription, $items ?>
<div class="mx-auto max-w-2xl text-center">
  <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl"><?= View::e($title) ?></h1>
  <?php if ($metaDescription): ?><p class="mt-3 text-lg leading-8 text-muted-foreground"><?= View::e($metaDescription) ?></p><?php endif ?>
</div>
<div class="mt-12">
  <?php if ($items): ?><?= View::render('Team::grid', ['items' => $items, 'columns' => 3]) ?>
  <?php else: ?><p class="rounded-lg border border-dashed px-6 py-14 text-center text-muted-foreground"><?= Lang::t('Henüz ekip üyesi eklenmedi.') ?></p><?php endif ?>
</div>
