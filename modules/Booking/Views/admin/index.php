<?php // Admin > Randevular. Değişkenler: $rows, $filter, $pending
use App\Modules\Booking\Services\Booking;
$badge = ['pending' => 'badge-yellow', 'confirmed' => 'badge-green', 'completed' => 'badge-gray', 'cancelled' => 'badge-gray', 'no_show' => 'badge-gray'];
$lastDay = null;
?>
<div class="mb-4 flex flex-wrap items-start justify-between gap-4">
  <div>
    <h1 class="text-2xl font-semibold tracking-tight">Randevular</h1>
    <p class="mt-1 text-sm text-muted-foreground"><?= $pending ? "$pending randevu onay bekliyor. " : '' ?>Randevu formu: <a href="<?= Router::url('booking.index') ?>" target="_blank" class="underline">/booking</a>, sayfalarda [booking].</p>
  </div>
  <a href="<?= Request::url('admin/modules/Booking/settings') ?>" class="btn btn-secondary">Çalışma saatleri ve kurallar</a>
</div>
<form class="mb-4 flex flex-wrap gap-2">
  <select name="view" class="input mt-0 w-auto" onchange="this.form.submit()">
    <?php foreach (['upcoming' => 'Yaklaşanlar', 'today' => 'Bugün', 'past' => 'Geçmiş', 'all' => 'Tümü'] as $k => $l): ?><option value="<?= $k ?>" <?= $filter['view'] === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach ?>
  </select>
  <select name="status" class="input mt-0 w-auto" onchange="this.form.submit()"><option value="">Her durum</option>
    <?php foreach (Booking::STATUSES as $k => $l): ?><option value="<?= $k ?>" <?= $filter['status'] === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach ?>
  </select>
  <input type="search" name="q" value="<?= View::e($filter['q']) ?>" placeholder="Ad, telefon, e-posta, hizmet…" class="input mt-0 max-w-xs">
</form>
<div class="card">
  <?php foreach ($rows['data'] as $a): $day = substr($a['starts_at'], 0, 10); ?>
    <?php if ($day !== $lastDay): $lastDay = $day; ?>
      <p class="border-b bg-muted/50 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-muted-foreground"><?= View::e(preg_replace('/, \d{2}:\d{2}$/', '', Booking::when($a['starts_at']))) ?></p>
    <?php endif ?>
    <a href="<?= Router::url('admin.booking.show', ['id' => $a['id']]) ?>" class="flex flex-wrap items-center gap-x-4 gap-y-1 border-b px-4 py-3 last:border-0 hover:bg-muted/50">
      <span class="w-24 font-mono text-sm font-semibold"><?= substr($a['starts_at'], 11, 5) ?>–<?= substr($a['ends_at'], 11, 5) ?></span>
      <span class="min-w-0 flex-1"><span class="font-medium"><?= View::e($a['name']) ?></span> <span class="text-sm text-muted-foreground">· <?= View::e($a['phone']) ?></span>
        <span class="block text-sm text-muted-foreground"><?= View::e($a['service_name']) ?><?= $a['staff_name'] ? ' · ' . View::e($a['staff_name']) : '' ?></span></span>
      <span class="badge <?= $badge[$a['status']] ?>"><?= Booking::STATUSES[$a['status']] ?></span>
    </a>
  <?php endforeach ?>
  <?php if (!$rows['data']): ?><p class="px-4 py-12 text-center text-muted-foreground">Randevu yok.</p><?php endif ?>
</div>
<div class="mt-4"><?= View::render('partials/pagination', ['page' => $rows]) ?></div>
