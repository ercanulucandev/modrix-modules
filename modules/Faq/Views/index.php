<?php // /faq. Tema ezebilir: app/Themes/{Tema}/modules/Faq/index.php. Değişkenler: $title, $metaDescription, $groups ?>
<div class="mx-auto max-w-3xl">
  <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl"><?= View::e($title) ?></h1>
  <?php if ($metaDescription): ?><p class="mt-3 text-lg leading-8 text-muted-foreground"><?= View::e($metaDescription) ?></p><?php endif ?>

  <?php if (!$groups): ?>
    <p class="mt-10 rounded-lg border border-dashed px-6 py-14 text-center text-muted-foreground"><?= Lang::t('Henüz soru eklenmedi.') ?></p>
  <?php endif ?>
  <?php foreach ($groups as $group => $items): ?>
    <?php if ($group !== ''): ?><h2 class="mt-10 text-xl font-semibold tracking-tight"><?= View::e($group) ?></h2><?php endif ?>
    <div class="mt-4"><?= View::render('Faq::list', ['items' => $items]) ?></div>
  <?php endforeach ?>
</div>
