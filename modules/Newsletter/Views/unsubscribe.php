<?php // Abonelikten çıkma onayı. Değişkenler: $row, $token ?>
<div class="mx-auto max-w-lg py-10 text-center">
  <h1 class="text-2xl font-semibold tracking-tight"><?= Lang::t('Abonelikten çık') ?></h1>
  <?php if (!$row): ?>
    <p class="mt-3 text-muted-foreground"><?= Lang::t('Bu bağlantı geçersiz.') ?></p>
  <?php elseif ($row['status'] === 'unsubscribed'): ?>
    <p class="mt-3 text-muted-foreground"><?= View::e($row['email']) ?> zaten abonelikten çıkmış.</p>
  <?php else: ?>
    <p class="mt-3 text-muted-foreground"><strong class="text-foreground"><?= View::e($row['email']) ?></strong> <?= Lang::t('adresine bülten gönderilmesin mi?') ?></p>
    <form method="post" action="<?= Router::url('newsletter.unsubscribe', ['token' => $token]) ?>" class="mt-6"><?= View::csrf() ?>
      <button class="btn btn-primary"><?= Lang::t('Evet, abonelikten çık') ?></button></form>
  <?php endif ?>
</div>
