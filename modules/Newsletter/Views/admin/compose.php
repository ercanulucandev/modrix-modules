<?php // Admin > Bülten > Gönder. Değişken: $count ?>
<div class="mb-6 flex items-center gap-2">
  <a href="<?= Router::url('admin.newsletter') ?>" class="btn btn-ghost size-9 p-0" aria-label="Bültene dön"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg></a>
  <h1 class="text-2xl font-semibold tracking-tight">Bülten gönder</h1>
</div>
<form method="post" action="<?= Router::url('admin.newsletter.send') ?>" class="card max-w-3xl space-y-5 p-6">
  <?= View::csrf() ?>
  <?= View::render('partials/field', ['name' => 'subject', 'label' => 'Konu', 'required' => true, 'value' => View::old('subject')]) ?>
  <?= View::render('partials/field', ['name' => 'message', 'label' => 'Mesaj', 'type' => 'textarea', 'rows' => 12, 'required' => true, 'value' => View::old('message'),
      'help' => 'Paragrafları boş satırla ayırın. Her e-postanın altına "Abonelikten çık" bağlantısı kendiliğinden eklenir.']) ?>
  <div class="flex flex-wrap items-center justify-between gap-3 border-t pt-5">
    <p class="text-sm text-muted-foreground"><?= $count ?> aboneye gönderilecek. Çok sayıda abonede birkaç dakika sürebilir.</p>
    <div class="flex gap-2">
      <button name="test" value="1" class="btn btn-secondary">Bana deneme gönder</button>
      <button class="btn btn-primary" <?= $count ? '' : 'disabled' ?> onclick="return confirm('<?= $count ?> aboneye gönderilsin mi?')">Gönder</button>
    </div>
  </div>
</form>
