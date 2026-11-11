/**
 * Sitedeki formlar (Formlar modülü): sayfa yenilenmeden gönderim, alan hataları, teşekkür mesajı.
 * JS kapalıysa form normal gönderilir (sunucu geldiği sayfaya mesajla döner).
 */
for (const form of document.querySelectorAll('[data-modrix-form]:not([data-ready])')) {
  form.dataset.ready = '';
  const submit = form.querySelector('[data-form-submit]');
  const success = form.querySelector('[data-form-success]');
  const error = form.querySelector('[data-form-error]');
  const label = submit.textContent;

  const clear = () => {
    form.querySelectorAll('[data-field-error]').forEach((e) => e.remove());
    form.querySelectorAll('.input-error').forEach((e) => e.classList.remove('input-error'));
    error.classList.add('hidden');
  };

  form.addEventListener('input', (e) => {
    const box = e.target.closest('[data-form-field]');
    box?.querySelector('[data-field-error]')?.remove();
    e.target.classList.remove('input-error');
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    clear();
    success.classList.add('hidden');
    submit.disabled = true;
    submit.textContent = 'Gönderiliyor…';

    try {
      const response = await fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
      });
      const payload = await response.json().catch(() => ({}));

      if (response.ok) {
        form.reset();
        form.querySelector('[data-form-fields]').classList.add('hidden');
        submit.classList.add('hidden');
        success.textContent = payload.message ?? 'Gönderildi.';
        success.classList.remove('hidden');
        success.scrollIntoView({ block: 'center', behavior: 'smooth' });
        return;
      }

      if (response.status === 422 && payload.errors) {
        let first = null;
        for (const [name, message] of Object.entries(payload.errors)) {
          const box = form.querySelector(`[data-form-field="${CSS.escape(name)}"]`);
          if (!box) continue;
          const p = Object.assign(document.createElement('p'), { className: 'field-error', textContent: message });
          p.dataset.fieldError = '';
          box.append(p);
          box.querySelector('.input')?.classList.add('input-error');
          first ??= box;
        }
        if (first) {
          first.scrollIntoView({ block: 'center', behavior: 'smooth' });
          first.querySelector('input, textarea, select')?.focus({ preventScroll: true });
          return;
        }
      }
      error.textContent = response.status === 419 ? 'Sayfanın süresi doldu; yenileyip tekrar deneyin.' : (payload.error ?? 'Gönderilemedi; lütfen tekrar deneyin.');
      error.classList.remove('hidden');
    } catch {
      error.textContent = 'Bağlantı hatası; lütfen tekrar deneyin.';
      error.classList.remove('hidden');
    } finally {
      submit.disabled = false;
      submit.textContent = label;
    }
  });
}
