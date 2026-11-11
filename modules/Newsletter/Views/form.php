<?php
// Abonelik formu (bölüm ve [newsletter]). Değişkenler: $button, $dark (koyu zemin için)
use App\Modules\Newsletter\Services\Newsletter;
$result = Session::flash('newsletter');
$consent = (string) Setting::get('newsletter_consent');
$policy = (string) Setting::get('newsletter_policy_url');
$id = 'nl-' . bin2hex(random_bytes(3));
?>
<form method="post" action="<?= Router::url('newsletter.subscribe') ?>" class="w-full max-w-md" data-newsletter>
  <?= View::csrf() ?>
  <input type="hidden" name="_t" value="<?= Newsletter::timeToken() ?>">
  <div style="position:absolute;left:-9999px" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
  <div class="flex gap-2">
    <label for="<?= $id ?>" class="sr-only"><?= Lang::t('E-posta adresiniz') ?></label>
    <input id="<?= $id ?>" type="email" name="email" required maxlength="190" placeholder="<?= View::e(Lang::t('E-posta adresiniz')) ?>" autocomplete="email"
           value="<?= View::e((string) Session::flash('newsletter_email')) ?>" class="input h-11 flex-1 <?= !empty($dark) ? 'border-white/20 bg-white/10 text-white placeholder:text-white/60' : '' ?>">
    <button class="btn btn-primary h-11 px-5"><?= View::e($button ?? 'Abone ol') ?></button>
  </div>
  <?php if ($consent !== ''): ?>
    <label class="mt-3 flex items-start gap-2 text-xs <?= !empty($dark) ? 'text-white/70' : 'text-muted-foreground' ?>">
      <input type="checkbox" name="consent" value="1" required class="mt-0.5 size-4 shrink-0">
      <span><?= View::e($consent) ?><?php if ($policy !== '' && Menu::validUrl($policy)): ?> <a href="<?= View::e(Menu::href($policy)) ?>" target="_blank" class="underline"><?= Lang::t('Aydınlatma metni') ?></a><?php endif ?></span>
    </label>
  <?php endif ?>
  <p class="mt-3 text-sm <?= $result ? ($result['ok'] ? 'text-emerald-600' : 'text-destructive') : 'hidden' ?>" data-newsletter-message role="status"><?= View::e($result['message'] ?? '') ?></p>
</form>
<?php if (empty($GLOBALS['modrix_newsletter_js'])): $GLOBALS['modrix_newsletter_js'] = true; ?>
<script>
// Sayfa yenilenmeden gönder (JS yoksa form normal gönderilir)
document.addEventListener('submit', async (e) => {
  const form = e.target.closest('[data-newsletter]'); if (!form) return;
  e.preventDefault();
  const msg = form.querySelector('[data-newsletter-message]'), btn = form.querySelector('button');
  btn.disabled = true;
  try {
    const res = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' } });
    const data = await res.json().catch(() => ({}));
    msg.textContent = data.message || data.error || 'Bir sorun oluştu.';
    msg.className = 'mt-3 text-sm ' + (res.ok ? 'text-emerald-600' : 'text-destructive');
    if (res.ok) form.reset();
  } catch { msg.textContent = 'Sunucuya ulaşılamadı.'; msg.className = 'mt-3 text-sm text-destructive'; }
  btn.disabled = false;
});
</script>
<?php endif ?>
