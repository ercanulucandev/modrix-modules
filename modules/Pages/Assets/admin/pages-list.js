/**
 * Admin > Sayfalar listesi (WordPress'teki gibi): ağaç sırasında sayfalar (alt sayfalar "—" ile girintili),
 * durum filtreleri, arama ve satır üzerine gelince işlemler: Düzenle · Görüntüle · Kopyala · Sil.
 * Sunucu: app/Modules/Pages/Controllers/Admin/PageApiController.php
 */
import { apiClient } from '@app/lib/app.js';
import { h, formatDate, debounce } from '@app/lib/dom.js';
import { confirmDialog } from '@app/lib/modal.js';
import { toast } from '@app/lib/toast.js';

const root = document.querySelector('[data-pages-list]');
const ref = Object.fromEntries([...root.querySelectorAll('[data-ref]')].map((el) => [el.dataset.ref, el]));
const api = apiClient('admin/api/pages');
const editUrl = (id) => root.dataset.editUrl.replace('{id}', id);
const state = { q: '', status: '' };

root.querySelectorAll('[data-filter]').forEach((button) => button.addEventListener('click', () => {
  root.querySelectorAll('[data-filter]').forEach((b) => b.toggleAttribute('data-on', b === button));
  state.status = button.dataset.filter;
  load();
}));
ref.search.addEventListener('input', debounce(() => { state.q = ref.search.value.trim(); load(); }, 250));

load();

async function load() {
  try {
    const { data, meta } = await api.get('/', { q: state.q, status: state.status });
    for (const [key, value] of Object.entries(meta.stats)) {
      const el = root.querySelector(`[data-count="${key}"]`);
      if (el) el.textContent = `(${value})`;
    }
    render(data, meta.front_page);
  } catch (err) {
    ref.rows.replaceChildren(h('tr', {}, h('td', { colspan: 4, class: 'py-10 text-center text-destructive' }, err.message)));
  }
}

function render(pages, frontId) {
  if (!pages.length) {
    const filtered = state.q || state.status;
    ref.rows.replaceChildren(h('tr', {}, h('td', { colspan: 4, class: 'py-14 text-center' }, [
      h('p', { class: 'font-medium' }, filtered ? 'Sonuç yok' : 'Henüz sayfa yok'),
      h('p', { class: 'mt-1 text-sm text-muted-foreground' }, filtered ? 'Farklı bir arama ya da filtre deneyin.' : 'İlk sayfanızı oluşturun: Hakkımızda, İletişim…'),
    ])));
    return;
  }

  ref.rows.replaceChildren(...pages.map((page) => {
    const action = (label, attrs) => h(attrs.href ? 'a' : 'button', { class: 'hover:underline', type: attrs.href ? null : 'button', ...attrs }, label);
    return h('tr', { class: 'group' }, [
      h('td', { class: 'min-w-64' }, [
        h('div', { class: 'flex items-baseline gap-2' }, [
          page.depth ? h('span', { class: 'text-muted-foreground', 'aria-hidden': 'true' }, '— '.repeat(page.depth)) : null,
          h('a', { href: editUrl(page.id), class: 'font-medium hover:underline' }, page.title),
          page.id === frontId ? h('span', { class: 'badge badge-default' }, 'Ana sayfa') : null,
        ]),
        h('p', { class: 'mt-0.5 font-mono text-xs text-muted-foreground' }, `/${page.path}`),
        // Satır işlemleri: masaüstünde üzerine gelince, dokunmatikte her zaman
        h('div', { class: 'mt-1 flex gap-3 text-xs text-muted-foreground opacity-100 transition md:opacity-0 md:group-hover:opacity-100 md:group-focus-within:opacity-100' }, [
          action('Düzenle', { href: editUrl(page.id), class: 'hover:underline hover:text-foreground' }),
          action(page.status === 'published' ? 'Görüntüle' : 'Önizle', { href: page.url, target: '_blank', class: 'hover:underline hover:text-foreground' }),
          action('Kopyala', { class: 'hover:underline hover:text-foreground', onClick: () => duplicate(page) }),
          action('Sil', { class: 'text-destructive hover:underline', onClick: () => remove(page) }),
        ]),
      ]),
      h('td', { class: 'hidden text-muted-foreground md:table-cell' }, page.template_label),
      h('td', {}, h('span', { class: `badge ${page.status === 'published' ? 'badge-green' : 'badge-yellow'}` }, page.status_label)),
      h('td', { class: 'hidden whitespace-nowrap text-muted-foreground sm:table-cell' }, formatDate(page.updated_at)),
    ]);
  }));
}

async function duplicate(page) {
  try {
    const { data, message } = await api.post(`/${page.id}/duplicate`);
    toast(message);
    window.location.assign(editUrl(data.id));
  } catch (err) {
    toast(err.message, 'error');
  }
}

async function remove(page) {
  const ok = await confirmDialog({
    title: 'Sayfa silinsin mi?',
    message: `"${page.title}" kalıcı olarak silinecek. Alt sayfaları varsa bir üst seviyeye taşınır. Menülerdeki bağlantıları elle kaldırmayı unutmayın.`,
    confirmText: 'Sil',
    danger: true,
  });
  if (!ok) return;
  try {
    const { message } = await api.delete(`/${page.id}`);
    toast(message);
    load();
  } catch (err) {
    toast(err.message, 'error');
  }
}
