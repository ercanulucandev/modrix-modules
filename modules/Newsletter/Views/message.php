<?php // Onay / ayrılma sonucu. Değişkenler: $title, $message ?>
<div class="mx-auto max-w-lg py-10 text-center">
  <h1 class="text-2xl font-semibold tracking-tight"><?= View::e($title) ?></h1>
  <p class="mt-3 text-muted-foreground"><?= View::e($message) ?></p>
  <a href="<?= Request::url('/') ?>" class="btn btn-secondary mt-6"><?= Lang::t('Ana sayfaya dön') ?></a>
</div>
