<?php // Admin > Randevu ayrıntısı. Değişken: $a
use App\Modules\Booking\Services\Booking;
?>
<div class="mb-6 flex items-center gap-2">
  <a href="<?= Router::url('admin.booking') ?>" class="btn btn-ghost size-9 p-0" aria-label="Randevulara dön"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg></a>
  <div><h1 class="text-2xl font-semibold tracking-tight"><?= View::e($a['name']) ?></h1><p class="text-sm text-muted-foreground"><?= View::e(Booking::when($a['starts_at'])) ?> – <?= substr($a['ends_at'], 11, 5) ?></p></div>
</div>
<div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
  <dl class="card divide-y text-sm">
    <?php foreach (['Hizmet' => $a['service_name'], 'Personel' => $a['staff_name'], 'Telefon' => $a['phone'], 'E-posta' => $a['email'], 'Ücret' => $a['price'] !== null ? number_format((float) $a['price'], 2, ',', '.') . ' ₺' : null, 'Müşteri notu' => $a['note'], 'Oluşturulma' => View::date($a['created_at'], true)] as $label => $value): if ($value === null || $value === '') continue; ?>
      <div class="flex justify-between gap-4 p-4"><dt class="text-muted-foreground"><?= $label ?></dt><dd class="text-right"><?= nl2br(View::e((string) $value)) ?></dd></div>
    <?php endforeach ?>
  </dl>
  <form method="post" action="<?= Router::url('admin.booking.update', ['id' => $a['id']]) ?>" class="card space-y-4 p-5">
    <?= View::csrf() ?>
    <?= View::render('partials/field', ['name' => 'status', 'label' => 'Durum', 'type' => 'select', 'value' => $a['status'], 'options' => Booking::STATUSES]) ?>
    <?= View::render('partials/field', ['name' => 'admin_note', 'label' => 'Not (sadece panelde)', 'type' => 'textarea', 'rows' => 2, 'value' => $a['admin_note']]) ?>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="notify" value="1" class="size-4" checked> Durum değişirse müşteriye e-posta gönder</label>
    <button class="btn btn-primary w-full">Kaydet</button>
    <div class="flex gap-2 border-t pt-4 text-sm"><a href="tel:<?= View::e($a['phone']) ?>" class="btn btn-secondary btn-sm flex-1">Ara</a><a href="mailto:<?= View::e($a['email']) ?>" class="btn btn-secondary btn-sm flex-1">E-posta</a></div>
  </form>
</div>
