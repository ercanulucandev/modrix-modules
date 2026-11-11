<?php // Admin > Bülten. Değişkenler: $rows, $counts, $filter
use App\Modules\Newsletter\Services\Newsletter;
$badges = ['active' => 'badge-green', 'pending' => 'badge-yellow', 'unsubscribed' => 'badge-gray'];
?>
<div class="mb-6 flex flex-wrap items-start justify-between gap-4">
  <div>
    <h1 class="text-2xl font-semibold tracking-tight">Bülten</h1>
    <p class="mt-1 text-sm text-muted-foreground"><?= $counts['active'] ?> abone<?= $counts['pending'] ? " · {$counts['pending']} onay bekliyor" : '' ?>. Form: ana sayfada "Bülten" bölümü, sayfalarda [newsletter].</p>
  </div>
  <div class="flex gap-2">
    <a href="<?= Router::url('admin.newsletter.export') ?>" class="btn btn-secondary">CSV indir</a>
    <a href="<?= Router::url('admin.newsletter.compose') ?>" class="btn btn-primary">Bülten gönder</a>
  </div>
</div>

<form class="mb-4 flex flex-wrap gap-2">
  <select name="status" class="input mt-0 w-auto" onchange="this.form.submit()">
    <option value="">Hepsi</option>
    <?php foreach (Newsletter::STATUSES as $k => $label): ?><option value="<?= $k ?>" <?= $filter['status'] === $k ? 'selected' : '' ?>><?= $label ?> (<?= $counts[$k] ?>)</option><?php endforeach ?>
  </select>
  <input type="search" name="q" value="<?= View::e($filter['q']) ?>" placeholder="E-postada ara…" class="input mt-0 max-w-xs">
</form>

<div class="card overflow-x-auto">
  <table class="table">
    <thead><tr><th>E-posta</th><th>Durum</th><th class="hidden md:table-cell">Kayıt</th><th class="hidden lg:table-cell">Sayfa</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($rows['data'] as $r): ?>
        <tr>
          <td class="font-medium"><?= View::e($r['email']) ?></td>
          <td><span class="badge <?= $badges[$r['status']] ?>"><?= Newsletter::STATUSES[$r['status']] ?></span></td>
          <td class="hidden whitespace-nowrap text-muted-foreground md:table-cell"><?= View::date($r['created_at'], true) ?></td>
          <td class="hidden max-w-48 truncate text-muted-foreground lg:table-cell"><?= View::e((string) $r['source']) ?></td>
          <td class="text-right"><form method="post" action="<?= Router::url('admin.newsletter.delete', ['id' => $r['id']]) ?>" onsubmit="return confirm('Silinsin mi?')"><?= View::csrf() ?><button class="btn btn-ghost btn-sm text-destructive">Sil</button></form></td>
        </tr>
      <?php endforeach ?>
      <?php if (!$rows['data']): ?><tr><td colspan="5" class="py-10 text-center text-muted-foreground">Abone yok.</td></tr><?php endif ?>
    </tbody>
  </table>
</div>
<div class="mt-4"><?= View::render('partials/pagination', ['page' => $rows]) ?></div>
