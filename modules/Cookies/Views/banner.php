<?php // Çerez bildirimi. Değişken: $categories ['analytics' => [ad, açıklama], ...]. Önbellek uyumlu: görünürlüğü JS çerezden belirler.
$policy = (string) Setting::get('cookies_policy_url');
$left = Setting::get('cookies_position') === 'left';
?>
<div id="modrix-consent" hidden role="dialog" aria-live="polite" aria-label="<?= View::e(Lang::t('Çerez tercihleri')) ?>"
     class="fixed z-[90] border bg-background text-foreground shadow-2xl <?= $left ? 'bottom-4 left-4 right-4 max-w-md rounded-xl sm:right-auto' : 'inset-x-0 bottom-0 border-x-0 border-b-0' ?>">
  <div class="<?= $left ? 'p-5' : 'wrap py-5' ?>">
    <div class="<?= $left ? '' : 'flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between' ?>">
      <p class="text-sm leading-6 text-muted-foreground"><?= View::e(Setting::get('cookies_text')) ?>
        <?php if ($policy !== '' && Menu::validUrl($policy)): ?><a href="<?= View::e(Menu::href($policy)) ?>" class="font-medium text-foreground underline"><?= Lang::t('Çerez politikası') ?></a><?php endif ?></p>
      <div class="flex shrink-0 flex-wrap gap-2 <?= $left ? 'mt-4' : '' ?>">
        <?php if ($categories): ?><button type="button" class="btn btn-ghost btn-sm" data-consent-toggle><?= Lang::t('Tercihler') ?></button><?php endif ?>
        <button type="button" class="btn btn-secondary btn-sm" data-consent-reject><?= View::e(Setting::get('cookies_reject')) ?></button>
        <button type="button" class="btn btn-primary btn-sm" data-consent-accept><?= View::e(Setting::get('cookies_accept')) ?></button>
      </div>
    </div>
    <?php if ($categories): ?>
      <div class="mt-4 hidden space-y-2 border-t pt-4" data-consent-panel>
        <label class="flex items-start gap-3 text-sm"><input type="checkbox" checked disabled class="mt-0.5 size-4"><span><span class="font-medium"><?= Lang::t('Zorunlu') ?></span><span class="block text-muted-foreground"><?= Lang::t('Sitenin çalışması için gerekli (oturum, güvenlik). Kapatılamaz.') ?></span></span></label>
        <?php foreach ($categories as $key => [$label, $desc]): ?>
          <label class="flex items-start gap-3 text-sm"><input type="checkbox" value="<?= $key ?>" class="mt-0.5 size-4" data-consent-category><span><span class="font-medium"><?= $label ?></span><span class="block text-muted-foreground"><?= $desc ?></span></span></label>
        <?php endforeach ?>
        <button type="button" class="btn btn-primary btn-sm mt-2" data-consent-save><?= Lang::t('Seçimlerimi kaydet') ?></button>
      </div>
    <?php endif ?>
  </div>
</div>
<script>
// Onay çerezi: modrix_consent = "analytics,functional" (6 ay). Onaylanan kategorinin bekleyen kodları çalıştırılır.
// Her yerden tercihleri açmak için: <a href="#cookie-settings">Çerez tercihleri</a>  ya da  ModrixConsent.open()
(() => {
  const box = document.getElementById('modrix-consent');
  const all = <?= json_encode(array_keys($categories)) ?>;
  const read = () => { const m = document.cookie.match(/(?:^|; )modrix_consent=([^;]*)/); return m ? decodeURIComponent(m[1]).split(',').filter(Boolean) : null; };
  const run = (allowed) => {
    document.querySelectorAll('script[type="text/plain"][data-consent]').forEach((old) => {
      if (!allowed.includes(old.dataset.consent) || old.dataset.ran) return;
      old.dataset.ran = '1';
      const s = document.createElement('script');
      for (const a of old.attributes) if (!['type', 'data-consent', 'data-src', 'data-ran'].includes(a.name)) s.setAttribute(a.name, a.value);
      if (old.dataset.src) s.src = old.dataset.src; else s.textContent = old.textContent;
      old.after(s);
    });
  };
  const save = (allowed) => {
    document.cookie = 'modrix_consent=' + encodeURIComponent(allowed.join(',') || 'none') + '; Max-Age=' + 60 * 60 * 24 * 180 + '; Path=/; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');
    box.hidden = true;
    run(allowed);
  };
  const open = () => {
    const current = read() ?? [];
    box.querySelectorAll('[data-consent-category]').forEach((c) => { c.checked = current.includes(c.value); });
    box.querySelector('[data-consent-panel]')?.classList.remove('hidden');
    box.hidden = false;
  };
  box.querySelector('[data-consent-accept]').onclick = () => save(all);
  box.querySelector('[data-consent-reject]').onclick = () => save([]);
  const toggle = box.querySelector('[data-consent-toggle]');
  if (toggle) toggle.onclick = () => box.querySelector('[data-consent-panel]').classList.toggle('hidden');
  const saveBtn = box.querySelector('[data-consent-save]');
  if (saveBtn) saveBtn.onclick = () => save([...box.querySelectorAll('[data-consent-category]:checked')].map((c) => c.value));
  document.addEventListener('click', (e) => { if (e.target.closest('a[href="#cookie-settings"]')) { e.preventDefault(); open(); } });
  window.ModrixConsent = { open, get: read };

  const current = read();
  if (current) run(current); else box.hidden = false;
})();
</script>
