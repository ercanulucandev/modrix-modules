<?php // /references. Değişkenler: $title, $metaDescription, $clients, $testimonials ?>
<div class="mx-auto max-w-2xl text-center">
  <h1 class="text-3xl font-semibold tracking-tight sm:text-4xl"><?= View::e($title) ?></h1>
  <?php if ($metaDescription): ?><p class="mt-3 text-lg leading-8 text-muted-foreground"><?= View::e($metaDescription) ?></p><?php endif ?>
</div>
<?php if ($clients): ?><div class="mt-12"><?= View::render('References::logos', ['items' => $clients]) ?></div><?php endif ?>
<?php if ($testimonials): ?><div class="mt-14"><?= View::render('References::testimonials', ['items' => $testimonials]) ?></div><?php endif ?>
<?php if (!$clients && !$testimonials): ?><p class="mt-12 rounded-lg border border-dashed px-6 py-14 text-center text-muted-foreground"><?= Lang::t('Henüz referans eklenmedi.') ?></p><?php endif ?>
