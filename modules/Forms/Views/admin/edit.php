<?php
// Admin > Formlar > Düzenle: form oluşturucu. Davranış: Assets/admin/form-builder.js
// Değişkenler: $form, $types
?>
<div data-form-builder
     data-api="<?= View::e(Request::url('admin/api/forms/' . $form['id'])) ?>"
     data-entries-url="<?= View::e(Router::url('admin.forms.entries', ['id' => $form['id']])) ?>">

  <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <div class="flex items-center gap-2">
      <a href="<?= Router::url('admin.forms.index') ?>" class="btn btn-ghost size-9 p-0" aria-label="Formlara dön" title="Formlara dön">
        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
      </a>
      <div class="leading-tight">
        <h1 class="text-lg font-semibold tracking-tight">Form oluşturucu</h1>
        <p class="text-xs text-muted-foreground" data-ref="state">&nbsp;</p>
      </div>
    </div>
    <div class="flex gap-2">
      <a href="<?= Router::url('admin.forms.entries', ['id' => $form['id']]) ?>" class="btn btn-secondary">Mesajlar</a>
      <button type="button" class="btn btn-primary" data-ref="save">Kaydet</button>
    </div>
  </div>

  <div class="grid items-start gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
    <!-- Alanlar -->
    <div class="min-w-0">
      <div class="card">
        <div class="flex flex-wrap items-end gap-3 border-b p-4">
          <div class="min-w-48 flex-1" data-field="name">
            <label class="label" for="form-name">Form adı</label>
            <input id="form-name" class="input" maxlength="100" data-ref="name">
          </div>
          <div class="w-56" data-field="slug">
            <label class="label" for="form-slug">Kısa kod</label>
            <div class="mt-2 flex items-center rounded-md border border-input font-mono text-xs shadow-xs">
              <span class="px-2 text-muted-foreground">[form</span>
              <input id="form-slug" class="h-9 min-w-0 flex-1 bg-transparent outline-none" maxlength="50" spellcheck="false" data-ref="slug">
              <span class="px-2 text-muted-foreground">]</span>
            </div>
          </div>
        </div>
        <div class="p-4">
          <div class="space-y-2" data-ref="fields"></div>
          <div class="mt-4 rounded-lg border border-dashed p-3" data-field="fields">
            <p class="mb-2 text-xs font-medium text-muted-foreground">Alan ekle</p>
            <div class="flex flex-wrap gap-1.5" data-ref="add">
              <?php foreach ($types as $key => $label): ?>
                <button type="button" class="btn btn-secondary btn-sm" data-type="<?= $key ?>">+ <?= $label ?></button>
              <?php endforeach ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Ayarlar -->
    <aside class="space-y-4 lg:sticky lg:top-20">
      <section class="card">
        <h2 class="card-title border-b px-4 py-3 text-sm">Gönderim sonrası</h2>
        <div class="space-y-4 p-4">
          <div><label class="label" for="s-button">Düğme yazısı</label><input id="s-button" class="input" maxlength="40" data-setting="button_text"></div>
          <div><label class="label" for="s-success">Teşekkür mesajı</label><textarea id="s-success" class="input" rows="3" maxlength="500" data-setting="success_message"></textarea></div>
          <label class="flex cursor-pointer items-center justify-between gap-3 text-sm">
            <span>Mesajları kaydet</span>
            <input type="checkbox" class="size-4 accent-primary" data-setting="store">
          </label>
        </div>
      </section>
      <section class="card">
        <h2 class="card-title border-b px-4 py-3 text-sm">E-posta bildirimi</h2>
        <div class="space-y-4 p-4">
          <div data-field="email_to">
            <label class="label" for="s-email">Kime</label>
            <input id="s-email" class="input" placeholder="Boşsa modül ayarındaki adres" data-setting="email_to">
            <p class="field-help text-xs">Birden çok adresi virgülle ayırın.</p>
          </div>
          <div><label class="label" for="s-subject">Konu</label><input id="s-subject" class="input" maxlength="150" data-setting="subject"></div>
        </div>
      </section>
      <section class="card p-4 text-sm leading-6 text-muted-foreground">
        <p class="font-medium text-foreground">Nasıl eklenir?</p>
        <p class="mt-1">Sayfa içeriğine kısa kodu tek satır olarak yazın: <code class="rounded bg-muted px-1 font-mono text-xs text-foreground" data-ref="code"></code></p>
        <p class="mt-1">Ana sayfa için: Görünüm &gt; Özelleştir &gt; Ana sayfa bölümleri &gt; Bölüm ekle &gt; Form.</p>
      </section>
    </aside>
  </div>
</div>

<script type="application/json" id="form-data"><?= json_encode(['form' => $form, 'types' => $types], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<script type="module" src="<?= View::e(Module::asset('Forms', 'admin/form-builder.js')) ?>"></script>
