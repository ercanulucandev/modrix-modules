/**
 * Admin > Yazılar listesi (WordPress'teki gibi): durum filtreleri (Tümü / Yayında / Zamanlanmış / Taslak), kategori,
 * arama, sayfalama ve satır üzerine gelince işlemler: Düzenle · Görüntüle · Kopyala · Sil. Düzenleme ayrı sayfada.
 * Sunucu: app/Modules/Blog/Controllers/Admin/PostApiController.php
 */
import { apiClient } from '@app/lib/app.js';
import { h, formatDate, debounce } from '@app/lib/dom.js';
import { confirmDialog } from '@app/lib/modal.js';
import { renderPagination } from '@app/lib/pagination.js';
import { toast } from '@app/lib/toast.js';

const root = document.querySelector('[data-posts-list]');
const ref = Object.fromEntries([...root.querySelectorAll('[data-ref]')].map((el) => [el.dataset.ref, el]));
const api = apiClient('admin/api/blog');
const editUrl = (id) => root.dataset.editUrl.replace('{id}', id);

// Durum adreste de durur (?status=draft&page=2): sayfa yenilenince ya da düzenleyiciden dönünce korunur
const params = new URLSearchParams(location.search);
const state = { q: params.get('q') ?? '', status: params.get('status') ?? '', category: params.get('category') ?? '', page: Number(params.get('page') ?? 1) };
const BADGES = { published: 'badge-green', scheduled: 'badge-default', draft: 'badge-yellow' };

ref.search.value = state.q;
ref.category.value = state.category;
root.querySelectorAll('[data-filter]').forEach((b) => b.toggleAttribute('data-on', b.dataset.filter === state.status));

root.querySelectorAll('[data-filter]').forEach((button) => button.addEventListener('click', () => {
  root.querySelectorAll('[data-filter]').forEach((b) => b.toggleAttribute('data-on', b === button));
  Object.assign(state, { status: button.dataset.filter, page: 1 });
  load();
}));
ref.category.addEventListener('change', () => { Object.assign(state, { category: ref.category.value, page: 1 }); load(); });
ref.search.addEventListener('input', debounce(() => { Object.assign(state, { q: ref.search.value.trim(), page: 1 }); load(); }, 250));

load();

async function load() {
  const query = new URLSearchParams(Object.entries(state).filter(([k, v]) => v && !(k === 'page' && v === 1)));
  history.replaceState(null, '', location.pathname + (query.toString() ? `?${query}` : ''));
  try {
    const { data, meta } = await api.get('/posts', state);
    for (const [key, value] of Object.entries(meta.stats)) {
      const el = root.querySelector(`[data-count="${key}"]`);
      if (el) el.textContent = `(${value})`;
    }
    render(data);
    renderPagination(ref.pagination, meta, (page) => { state.page = page; load(); window.scrollTo({ top: 0, behavior: 'smooth' }); });
  } catch (err) {
    ref.rows.replaceChildren(h('tr', {}, h('td', { colspan: 5, class: 'py-10 text-center text-destructive' }, err.message)));
  }
}

function render(posts) {
  if (!posts.length) {
    const filtered = state.q || state.status || state.category;
    ref.rows.replaceChildren(h('tr', {}, h('td', { colspan: 5, class: 'py-14 text-center' }, [
      h('p', { class: 'font-medium' }, filtered ? 'Sonuç yok' : 'Henüz yazı yok'),
      h('p', { class: 'mt-1 text-sm text-muted-foreground' }, filtered ? 'Farklı bir arama ya da filtre deneyin.' : 'İlk yazınızı oluşturun.'),
    ])));
    return;
  }
  ref.rows.replaceChildren(...posts.map((post) => {
    const link = (label, attrs) => h(attrs.href ? 'a' : 'button', { type: attrs.href ? null : 'button', ...attrs }, label);
    return h('tr', { class: 'group' }, [
      h('td', { class: 'min-w-72' }, [
        h('div', { class: 'flex items-center gap-3' }, [
          post.thumb
            ? h('img', { src: post.thumb, alt: '', loading: 'lazy', class: 'size-10 shrink-0 rounded-md border object-cover' })
            : h('span', { class: 'flex size-10 shrink-0 items-center justify-center rounded-md border bg-muted text-xs font-semibold text-muted-foreground' }, post.title.slice(0, 1).toUpperCase()),
          h('div', { class: 'min-w-0' }, [
            h('a', { href: editUrl(post.id), class: 'font-medium hover:underline' }, post.title),
            h('div', { class: 'mt-1 flex gap-3 text-xs text-muted-foreground opacity-100 transition md:opacity-0 md:group-hover:opacity-100 md:group-focus-within:opacity-100' }, [
              link('Düzenle', { href: editUrl(post.id), class: 'hover:text-foreground hover:underline' }),
              link(post.state === 'published' ? 'Görüntüle' : 'Önizle', { href: post.url, target: '_blank', class: 'hover:text-foreground hover:underline' }),
              link('Kopyala', { class: 'hover:text-foreground hover:underline', onClick: () => duplicate(post) }),
              link('Sil', { class: 'text-destructive hover:underline', onClick: () => remove(post) }),
            ]),
          ]),
        ]),
      ]),
      h('td', { class: 'hidden text-muted-foreground md:table-cell' }, post.category ?? '—'),
      h('td', {}, h('span', { class: `badge ${BADGES[post.state]}` }, post.state_label)),
      h('td', { class: 'hidden whitespace-nowrap text-muted-foreground sm:table-cell' }, post.published_at ? formatDate(post.published_at) : `Düzenleme: ${formatDate(post.updated_at, false)}`),
      h('td', { class: 'hidden tabular-nums text-muted-foreground lg:table-cell' }, post.views),
    ]);
  }));
}

async function duplicate(post) {
  try {
    const { data, message } = await api.post(`/posts/${post.id}/duplicate`);
    toast(message);
    location.assign(editUrl(data.id));
  } catch (err) {
    toast(err.message, 'error');
  }
}

async function remove(post) {
  const ok = await confirmDialog({ title: 'Yazı silinsin mi?', message: `"${post.title}" kalıcı olarak silinecek.`, confirmText: 'Sil', danger: true });
  if (!ok) return;
  try {
    toast((await api.delete(`/posts/${post.id}`)).message);
    load();
  } catch (err) {
    toast(err.message, 'error');
  }
}
