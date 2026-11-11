<?php
// Admin > Formlar: liste. Değişken: $forms (entries, unread sayılarıyla)
?>
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
  <div>
    <h1 class="text-2xl font-semibold tracking-tight">Formlar</h1>
    <p class="mt-1 text-sm text-muted-foreground">İletişim ve başvuru formları. Bir sayfaya eklemek için içerikte kısa kodu yazın, ana sayfaya "Form" bölümüyle ekleyin.</p>
  </div>
  <form method="post" action="<?= Router::url('admin.forms.create') ?>"><?= View::csrf() ?>
    <button class="btn btn-primary"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg> Yeni form</button>
  </form>
</div>

<?php if (!$forms): ?>
  <div class="rounded-lg border border-dashed px-6 py-16 text-center text-sm text-muted-foreground">Henüz form yok.</div>
<?php else: ?>
  <div class="card overflow-x-auto">
    <table class="table">
      <thead><tr><th>Form</th><th>Kısa kod</th><th>Mesajlar</th><th class="text-right">İşlem</th></tr></thead>
      <tbody>
        <?php foreach ($forms as $f): $code = '[form ' . $f['slug'] . ']'; ?>
          <tr>
            <td><a href="<?= Router::url('admin.forms.edit', ['id' => $f['id']]) ?>" class="font-medium hover:underline"><?= View::e($f['name']) ?></a></td>
            <td>
              <button type="button" class="badge badge-gray font-mono hover:bg-accent" title="Kopyala"
                      onclick="navigator.clipboard.writeText(this.textContent.trim()).then(() => { this.dataset.copied = ''; setTimeout(() => delete this.dataset.copied, 1200) })"
                      ><?= View::e($code) ?></button>
            </td>
            <td>
              <a href="<?= Router::url('admin.forms.entries', ['id' => $f['id']]) ?>" class="inline-flex items-center gap-2 hover:underline">
                <?= (int) $f['entries'] ?> mesaj
                <?php if ((int) $f['unread']): ?><span class="badge badge-default"><?= (int) $f['unread'] ?> yeni</span><?php endif ?>
              </a>
            </td>
            <td>
              <div class="flex justify-end gap-2">
                <a href="<?= Router::url('admin.forms.entries', ['id' => $f['id']]) ?>" class="btn btn-secondary btn-sm">Mesajlar</a>
                <a href="<?= Router::url('admin.forms.edit', ['id' => $f['id']]) ?>" class="btn btn-secondary btn-sm">Düzenle</a>
                <form method="post" action="<?= Router::url('admin.forms.delete', ['id' => $f['id']]) ?>" data-confirm="<?= View::e($f['name']) ?> ve tüm mesajları kalıcı olarak silinsin mi?">
                  <?= View::csrf() ?><button class="btn btn-danger-ghost btn-sm">Sil</button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
<?php endif ?>
<p class="mt-4 text-xs text-muted-foreground">E-posta bildirimi için Modüller &gt; Formlar &gt; Ayarlar. Sunucuda e-posta gönderimi ayarlı değilse mesajlar yine burada birikir.</p>
<style>[data-copied]::after { content: ' ✓ kopyalandı'; font-family: ui-sans-serif, system-ui; }</style>
