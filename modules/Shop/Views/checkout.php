<?php // Ödeme. Değişkenler: $cart, $methods, $codFee (kuruş)
use App\Modules\Shop\Services\Shop;
$terms = (string) Setting::get('shop_terms_url');
$f = fn(string $name, string $label, array $o = []) => View::render('partials/field', ['name' => $name, 'label' => $label, 'value' => View::old($name)] + $o);
?>
<h1 class="text-3xl font-semibold tracking-tight"><?= Lang::t('Ödeme') ?></h1>
<form method="post" action="<?= Router::url('shop.checkout.store') ?>" class="mt-8 grid items-start gap-8 lg:grid-cols-[1fr_22rem]" data-checkout>
  <?= View::csrf() ?>
  <div class="space-y-6">
    <section class="card space-y-4 p-6">
      <h2 class="font-semibold"><?= Lang::t('İletişim') ?></h2>
      <?= $f('name', 'Ad soyad', ['required' => true, 'attrs' => 'autocomplete="name"']) ?>
      <div class="grid gap-4 sm:grid-cols-2">
        <?= $f('email', 'E-posta', ['type' => 'email', 'required' => true, 'attrs' => 'autocomplete="email"', 'help' => 'Sipariş bilgileri bu adrese gelir.']) ?>
        <?= $f('phone', 'Telefon', ['type' => 'tel', 'required' => true, 'attrs' => 'autocomplete="tel"', 'placeholder' => '05xx xxx xx xx']) ?>
      </div>
    </section>
    <section class="card space-y-4 p-6">
      <h2 class="font-semibold"><?= Lang::t('Teslimat adresi') ?></h2>
      <div class="grid gap-4 sm:grid-cols-3">
        <?= $f('city', 'İl', ['required' => true, 'attrs' => 'autocomplete="address-level1"']) ?>
        <?= $f('district', 'İlçe', ['required' => true, 'attrs' => 'autocomplete="address-level2"']) ?>
        <?= $f('zip', 'Posta kodu', ['attrs' => 'autocomplete="postal-code"']) ?>
      </div>
      <?= $f('address', 'Adres', ['type' => 'textarea', 'required' => true, 'rows' => 3, 'attrs' => 'autocomplete="street-address"', 'placeholder' => 'Mahalle, sokak, bina no, daire']) ?>
      <details class="rounded-md border p-4 text-sm">
        <summary class="cursor-pointer font-medium"><?= Lang::t('Kurumsal fatura istiyorum') ?></summary>
        <div class="mt-4 grid gap-4 sm:grid-cols-3">
          <?= $f('company', 'Firma adı') ?><?= $f('tax_office', 'Vergi dairesi') ?><?= $f('tax_number', 'Vergi no') ?>
        </div>
      </details>
      <?= $f('note', 'Sipariş notu', ['type' => 'textarea', 'rows' => 2, 'help' => 'İsteğe bağlı.']) ?>
    </section>
    <section class="card space-y-3 p-6" data-field="payment">
      <h2 class="font-semibold"><?= Lang::t('Ödeme yöntemi') ?></h2>
      <?php $first = true; foreach ($methods as $key => $label): ?>
        <label class="flex cursor-pointer items-center gap-3 rounded-md border p-4 has-[:checked]:border-primary">
          <input type="radio" name="payment" value="<?= $key ?>" <?= (View::old('payment') ?? ($first ? $key : null)) === $key ? 'checked' : '' ?> class="size-4" data-fee="<?= $key === 'cod' ? $codFee : 0 ?>">
          <span class="flex-1 font-medium"><?= View::e($label) ?></span>
          <?php if ($key === 'cod' && $codFee): ?><span class="text-sm text-muted-foreground">+<?= Shop::money($codFee / 100) ?></span><?php endif ?>
        </label>
      <?php $first = false; endforeach ?>
      <?php if ($e = View::error('payment')): ?><p class="field-error"><?= View::e($e) ?></p><?php endif ?>
    </section>
  </div>

  <aside class="card space-y-4 p-5 lg:sticky lg:top-24">
    <h2 class="font-semibold"><?= Lang::t('Sipariş özeti') ?></h2>
    <ul class="space-y-2 text-sm">
      <?php foreach ($cart['lines'] as $l): ?><li class="flex justify-between gap-3"><span class="min-w-0"><?= View::e($l['name']) ?> <span class="text-muted-foreground">× <?= $l['qty'] ?></span></span><span class="shrink-0"><?= Shop::money($l['total'] / 100) ?></span></li><?php endforeach ?>
    </ul>
    <div class="border-t pt-4" data-totals data-base="<?= $cart['subtotal'] + $cart['shipping'] ?>">
      <?= View::render('Shop::totals', ['subtotal' => $cart['subtotal'], 'shipping' => $cart['shipping'], 'total' => $cart['subtotal'] + $cart['shipping']]) ?>
    </div>
    <label class="flex items-start gap-2 text-xs text-muted-foreground" data-field="terms">
      <input type="checkbox" name="terms" value="1" required class="mt-0.5 size-4 shrink-0">
      <span><?php if ($terms !== '' && Menu::validUrl($terms)): ?><a href="<?= View::e(Menu::href($terms)) ?>" target="_blank" class="underline"><?= Lang::t('Ön bilgilendirme formunu ve mesafeli satış sözleşmesini') ?></a><?php else: ?>Ön bilgilendirme formunu ve mesafeli satış sözleşmesini<?php endif ?> okudum, kabul ediyorum.</span>
    </label>
    <?php if ($e = View::error('terms')): ?><p class="field-error"><?= View::e($e) ?></p><?php endif ?>
    <button class="btn btn-primary h-11 w-full"><?= Lang::t('Siparişi onayla') ?></button>
    <p class="text-center text-xs text-muted-foreground"><?= Lang::t('Kartla ödemede güvenli ödeme sayfasına yönlendirilirsiniz.') ?></p>
  </aside>
</form>
<script>
  // Kapıda ödeme ücreti seçilince toplamı güncelle
  (() => {
    const box = document.querySelector('[data-totals]'), total = box.querySelector('[data-total]'), base = +box.dataset.base;
    const fmt = (c) => (c / 100).toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ₺';
    const update = () => { const r = document.querySelector('[name=payment]:checked'); total.textContent = fmt(base + (r ? +r.dataset.fee : 0)); };
    document.querySelectorAll('[name=payment]').forEach((r) => r.addEventListener('change', update));
    update();
  })();
</script>
