<a href="<?= Router::url('shop.cart') ?>" class="relative flex items-center gap-2 text-sm opacity-80 hover:opacity-100" aria-label="<?= View::e(Lang::t('Sepet')) ?>" title="<?= View::e(Lang::t('Sepet')) ?>">
  <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
  <span data-cart-count hidden class="absolute -right-2 -top-2 min-w-4 rounded-full bg-red-600 px-1 text-center text-[10px] font-semibold leading-4 text-white"></span>
  <span class="md:sr-only"><?= Lang::t('Sepet') ?></span>
</a>
<script>(() => { const m = document.cookie.match(/(?:^|; )modrix_cart=(\d+)/); const n = m ? +m[1] : 0; document.querySelectorAll('[data-cart-count]').forEach((el) => { el.textContent = n > 99 ? '99+' : n; el.hidden = !n; }); })();</script>
