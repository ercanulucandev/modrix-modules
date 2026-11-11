<?php
// Admin > Formlar > Mesajlar. Değişkenler: $form, $entries (sayfalı), $unreadOnly, $unread
?>
<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
  <div class="flex items-center gap-2">
    <a href="<?= Router::url('admin.forms.index') ?>" class="btn btn-ghost size-9 p-0" aria-label="Formlara dön" title="Formlara dön">
      <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
    </a>
    <div>
      <h1 class="text-2xl font-semibold tracking-tight"><?= View::e($form['name']) ?></h1>
      <p class="mt-1 text-sm text-muted-foreground"><?= (int) $entries['total'] ?> mesaj<?= $unread ? ' · <span data-unread>' . $unread . '</span> okunmamış' : '' ?></p>
    </div>
  </div>
  <div class="flex flex-wrap gap-2">
    <a href="?<?= $unreadOnly ? '' : 'filter=unread' ?>" class="btn btn-secondary btn-sm"><?= $unreadOnly ? 'Tümünü göster' : 'Sadece okunmamışlar' ?></a>
    <?php if ($unread): ?>
      <form method="post" action="<?= Router::url('admin.forms.readAll', ['id' => $form['id']]) ?>"><?= View::csrf() ?><button class="btn btn-secondary btn-sm">Tümünü okundu yap</button></form>
    <?php endif ?>
    <a href="<?= Router::url('admin.forms.export', ['id' => $form['id']]) ?>" class="btn btn-secondary btn-sm">Excel (CSV) indir</a>
    <a href="<?= Router::url('admin.forms.edit', ['id' => $form['id']]) ?>" class="btn btn-secondary btn-sm">Formu düzenle</a>
  </div>
</div>

<?php if (!$entries['data']): ?>
  <div class="rounded-lg border border-dashed px-6 py-16 text-center text-sm text-muted-foreground"><?= $unreadOnly ? 'Okunmamış mesaj yok.' : 'Henüz mesaj gelmedi.' ?></div>
<?php else: ?>
  <div class="space-y-2" data-entries data-read-url="<?= View::e(Request::url('admin/forms/' . $form['id'] . '/entries/{id}/read')) ?>">
    <?php foreach ($entries['data'] as $e):
      $values = array_values(array_filter($e['data'], fn($d) => $d['value'] !== '' && $d['value'] !== 'Hayır'));
      $preview = implode(' · ', array_map(fn($d) => mb_strimwidth($d['value'], 0, 60, '…'), array_slice($values, 0, 3)));
    ?>
      <details class="card group overflow-hidden <?= $e['is_read'] ? '' : 'border-primary/40' ?>" data-id="<?= (int) $e['id'] ?>" <?= $e['is_read'] ? '' : 'data-unread' ?>>
        <summary class="flex cursor-pointer list-none items-center gap-3 px-4 py-3 [&::-webkit-details-marker]:hidden">
          <span class="size-2 shrink-0 rounded-full <?= $e['is_read'] ? 'bg-transparent' : 'bg-primary' ?>" data-dot></span>
          <span class="min-w-0 flex-1 truncate text-sm <?= $e['is_read'] ? 'text-muted-foreground' : 'font-medium' ?>" data-preview><?= View::e($preview ?: '(boş)') ?></span>
          <span class="shrink-0 text-xs text-muted-foreground"><?= View::date($e['created_at'], true) ?></span>
        </summary>
        <div class="border-t px-4 py-4">
          <dl class="grid gap-x-6 gap-y-3 sm:grid-cols-[12rem_1fr]">
            <?php foreach ($e['data'] as $item): ?>
              <dt class="text-sm text-muted-foreground"><?= View::e($item['label']) ?></dt>
              <dd class="whitespace-pre-line break-words text-sm">
                <?php if ($item['value'] !== '' && filter_var($item['value'], FILTER_VALIDATE_EMAIL)): ?>
                  <a href="mailto:<?= View::e($item['value']) ?>" class="underline underline-offset-4"><?= View::e($item['value']) ?></a>
                <?php else: ?><?= $item['value'] !== '' ? View::e($item['value']) : '<span class="text-muted-foreground">—</span>' ?><?php endif ?>
              </dd>
            <?php endforeach ?>
          </dl>
          <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t pt-3 text-xs text-muted-foreground">
            <span><?= $e['page_url'] ? 'Sayfa: ' . View::e($e['page_url']) . ' · ' : '' ?>IP: <?= View::e((string) $e['ip']) ?></span>
            <span class="flex gap-2">
              <button type="button" class="btn btn-ghost btn-sm" data-mark-unread>Okunmadı yap</button>
              <form method="post" action="<?= Router::url('admin.forms.entryDelete', ['id' => $form['id'], 'entryId' => $e['id']]) ?>" data-confirm="Bu mesaj kalıcı olarak silinsin mi?">
                <?= View::csrf() ?><button class="btn btn-danger-ghost btn-sm">Sil</button>
              </form>
            </span>
          </div>
        </div>
      </details>
    <?php endforeach ?>
  </div>
  <div class="mt-6"><?= View::render('partials/pagination', ['page' => $entries]) ?></div>
<?php endif ?>

<script type="module">
  // Mesaj açılınca okundu, "Okunmadı yap" ile geri al
  import { config } from '@app/lib/app.js';
  const list = document.querySelector('[data-entries]');
  const mark = async (box, read) => {
    const body = new FormData();
    body.append('_token', config.csrf);
    body.append('read', read ? '1' : '');
    await fetch(list.dataset.readUrl.replace('{id}', box.dataset.id), { method: 'POST', body, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
    box.toggleAttribute('data-unread', !read);
    box.classList.toggle('border-primary/40', !read);
    box.querySelector('[data-dot]').classList.toggle('bg-primary', !read);
    box.querySelector('[data-preview]').classList.toggle('font-medium', !read);
  };
  list?.addEventListener('toggle', (e) => { if (e.target.open && e.target.hasAttribute('data-unread')) mark(e.target, true); }, true);
  list?.addEventListener('click', (e) => {
    const button = e.target.closest('[data-mark-unread]');
    if (!button) return;
    const box = button.closest('details');
    box.open = false;
    mark(box, false);
  });
</script>
