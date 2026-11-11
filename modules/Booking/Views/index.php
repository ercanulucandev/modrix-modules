<?php // /booking. Tema ezebilir: app/Themes/{Tema}/modules/Booking/index.php. Değişkenler: $title, $metaDescription ?>
<div class="mx-auto max-w-3xl">
  <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl"><?= View::e($title) ?></h1>
  <?php if ($metaDescription): ?><p class="mt-3 text-lg leading-8 text-muted-foreground"><?= View::e($metaDescription) ?></p><?php endif ?>
  <div class="mt-8"><?= View::render('Booking::widget') ?></div>
</div>
