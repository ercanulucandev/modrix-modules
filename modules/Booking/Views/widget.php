<?php
// Randevu formu (sayfa, [booking] ve bölüm ortak): hizmet -> kişi -> gün -> saat -> bilgiler
use App\Modules\Booking\Services\Booking;
use App\Services\Collection;
$services = Booking::services();
$staff = Booking::staff();
$days = Booking::days();
$dayNames = [1 => 'Pzt', 2 => 'Sal', 3 => 'Çar', 4 => 'Per', 5 => 'Cum', 6 => 'Cmt', 7 => 'Paz'];
$months = [1 => 'Oca', 2 => 'Şub', 3 => 'Mar', 4 => 'Nis', 5 => 'May', 6 => 'Haz', 7 => 'Tem', 8 => 'Ağu', 9 => 'Eyl', 10 => 'Eki', 11 => 'Kas', 12 => 'Ara'];
if (!$services): ?>
  <p class="rounded-lg border border-dashed px-6 py-12 text-center text-muted-foreground"><?= Lang::t('Şu an randevu alınamıyor.') ?></p>
<?php return; endif ?>
<form method="post" action="<?= Router::url('booking.store') ?>" class="space-y-6" data-booking data-slots-url="<?= View::e(Router::url('booking.slots')) ?>">
  <?= View::csrf() ?>
  <div style="position:absolute;left:-9999px" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>

  <fieldset class="space-y-2">
    <legend class="mb-2 font-semibold">1. <?= Lang::t('Hizmet') ?></legend>
    <?php foreach ($services as $i => $s): ?>
      <label class="flex cursor-pointer items-center gap-3 rounded-lg border p-4 has-[:checked]:border-primary has-[:checked]:bg-muted/50">
        <input type="radio" name="service_id" value="<?= $s['id'] ?>" required class="size-4" <?= ((int) View::old('service_id', count($services) === 1 ? $s['id'] : 0)) === $s['id'] ? 'checked' : '' ?>>
        <span class="flex-1"><span class="font-medium"><?= View::e($s['name']) ?></span>
          <?php if ($s['description']): ?><span class="block text-sm text-muted-foreground"><?= View::e($s['description']) ?></span><?php endif ?></span>
        <span class="text-right text-sm text-muted-foreground"><?= (int) $s['duration'] ?> <?= Lang::t('dk') ?><?php if (($s['price'] ?? '') !== ''): ?><span class="block font-medium text-foreground"><?= number_format((float) $s['price'], 2, ',', '.') ?> ₺</span><?php endif ?></span>
      </label>
    <?php endforeach ?>
  </fieldset>

  <?php if (count($staff) > 1): ?>
    <fieldset>
      <legend class="mb-2 font-semibold">2. <?= Lang::t('Kiminle') ?></legend>
      <select name="staff_id" class="input mt-0" data-staff>
        <option value=""><?= Lang::t('Fark etmez (ilk uygun)') ?></option>
        <?php foreach ($staff as $p): ?><option value="<?= $p['id'] ?>" <?= (int) View::old('staff_id') === $p['id'] ? 'selected' : '' ?>><?= View::e($p['name']) ?><?= $p['title'] ? ' · ' . View::e($p['title']) : '' ?></option><?php endforeach ?>
      </select>
    </fieldset>
  <?php endif ?>

  <fieldset>
    <legend class="mb-2 font-semibold"><?= count($staff) > 1 ? 3 : 2 ?>. <?= Lang::t('Gün ve saat') ?></legend>
    <div class="flex flex-wrap gap-2" data-days>
      <?php foreach ($days as $d): $t = strtotime($d); ?>
        <label class="cursor-pointer">
          <input type="radio" name="date" value="<?= $d ?>" class="peer sr-only" required <?= View::old('date') === $d ? 'checked' : '' ?>>
          <span class="flex w-16 flex-col items-center rounded-lg border py-2 text-sm peer-checked:border-primary peer-checked:bg-primary peer-checked:text-primary-foreground">
            <span class="text-xs opacity-75"><?= Lang::t($dayNames[(int) date('N', $t)]) ?></span>
            <span class="text-lg font-semibold leading-tight"><?= date('j', $t) ?></span>
            <span class="text-xs opacity-75"><?= Lang::t($months[(int) date('n', $t)]) ?></span>
          </span>
        </label>
      <?php endforeach ?>
    </div>
    <div class="mt-4 flex flex-wrap gap-2" data-slots><p class="text-sm text-muted-foreground"><?= Lang::t('Önce hizmeti ve günü seçin.') ?></p></div>
    <input type="hidden" name="time" value="<?= View::e((string) View::old('time')) ?>" data-time>
    <?php if ($e = View::error('time')): ?><p class="field-error"><?= View::e($e) ?></p><?php endif ?>
  </fieldset>

  <fieldset class="space-y-4">
    <legend class="mb-2 font-semibold"><?= count($staff) > 1 ? 4 : 3 ?>. <?= Lang::t('Bilgileriniz') ?></legend>
    <?= View::render('partials/field', ['name' => 'name', 'label' => Lang::t('Ad soyad'), 'required' => true, 'value' => View::old('name'), 'attrs' => 'autocomplete="name"']) ?>
    <div class="grid gap-4 sm:grid-cols-2">
      <?= View::render('partials/field', ['name' => 'email', 'label' => Lang::t('E-posta'), 'type' => 'email', 'required' => true, 'value' => View::old('email'), 'attrs' => 'autocomplete="email"']) ?>
      <?= View::render('partials/field', ['name' => 'phone', 'label' => Lang::t('Telefon'), 'type' => 'tel', 'required' => true, 'value' => View::old('phone'), 'attrs' => 'autocomplete="tel"']) ?>
    </div>
    <?= View::render('partials/field', ['name' => 'note', 'label' => Lang::t('Not'), 'type' => 'textarea', 'rows' => 2, 'value' => View::old('note'), 'help' => Lang::t('İsteğe bağlı.')]) ?>
  </fieldset>
  <button class="btn btn-primary h-11 w-full sm:w-auto sm:px-8"><?= Lang::t('Randevuyu oluştur') ?></button>
</form>
<?php if (empty($GLOBALS['modrix_booking_js'])): $GLOBALS['modrix_booking_js'] = true; ?>
<script>
// Hizmet, kişi ya da gün değişince o günün boş saatleri sunucudan gelir
document.querySelectorAll('[data-booking]').forEach((form) => {
  const box = form.querySelector('[data-slots]'), time = form.querySelector('[data-time]');
  const msg = (t) => { box.innerHTML = ''; const p = document.createElement('p'); p.className = 'text-sm text-muted-foreground'; p.textContent = t; box.append(p); };
  const load = async () => {
    const service = form.querySelector('[name=service_id]:checked')?.value, date = form.querySelector('[name=date]:checked')?.value;
    if (!service || !date) return;
    msg(<?= json_encode(Lang::t('Saatler yükleniyor…')) ?>);
    const q = new URLSearchParams({ service, date, staff: form.querySelector('[data-staff]')?.value || '' });
    try {
      const { slots } = await (await fetch(form.dataset.slotsUrl + '?' + q, { headers: { Accept: 'application/json' } })).json();
      if (!slots.length) { time.value = ''; return msg(<?= json_encode(Lang::t('Bu günde boş saat kalmadı; başka bir gün seçin.')) ?>); }
      box.innerHTML = '';
      slots.forEach((s) => {
        const b = document.createElement('button');
        b.type = 'button'; b.textContent = s;
        b.className = 'rounded-md border px-3 py-2 text-sm font-medium tabular-nums hover:border-primary';
        if (s === time.value) b.classList.add('bg-primary', 'text-primary-foreground');
        b.onclick = () => { time.value = s; box.querySelectorAll('button').forEach((x) => x.classList.toggle('bg-primary', x === b)); box.querySelectorAll('button').forEach((x) => x.classList.toggle('text-primary-foreground', x === b)); };
        box.append(b);
      });
      if (!slots.includes(time.value)) time.value = '';
    } catch { msg(<?= json_encode(Lang::t('Saatler yüklenemedi; sayfayı yenileyin.')) ?>); }
  };
  form.addEventListener('change', (e) => { if (e.target.matches('[name=service_id], [name=date], [data-staff]')) load(); });
  form.addEventListener('submit', (e) => { if (!time.value) { e.preventDefault(); msg(<?= json_encode(Lang::t('Lütfen bir saat seçin.')) ?>); box.scrollIntoView({ block: 'center' }); } });
  load();
});
</script>
<?php endif ?>
