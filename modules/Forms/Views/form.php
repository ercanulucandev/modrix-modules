<?php
// Sitedeki form. Her temada çalışır (ortak sınıflar: input, label, btn). Tema ezebilir: app/Themes/{Tema}/modules/Forms/form.php
// Değişkenler: $form, $compact (isteğe bağlı: daha sıkı aralıklar)
use App\Modules\Forms\Services\FormService;

$success = Session::flash('form_success_' . $form['slug']);
$uid = 'f' . substr(md5($form['slug'] . microtime()), 0, 6);
?>
<form method="post" action="<?= Router::url('forms.submit', ['slug' => $form['slug']]) ?>" id="form-<?= View::e($form['slug']) ?>"
      class="modrix-form not-prose scroll-mt-24" data-modrix-form novalidate>
  <?= View::csrf() ?>
  <input type="hidden" name="_t" value="<?= View::e(FormService::timeToken()) ?>">
  <?php // Tuzak alan: insanlar görmez, botlar doldurur ?>
  <div aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden">
    <label><?= Lang::t('Web siteniz') ?> <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
  </div>

  <div class="alert alert-ok <?= $success ? '' : 'hidden' ?>" role="status" data-form-success><?= View::e((string) $success) ?></div>
  <div class="alert alert-error hidden" role="alert" data-form-error></div>

  <div class="grid gap-x-4 gap-y-5 sm:grid-cols-2" data-form-fields>
    <?php foreach ($form['fields'] as $f): $id = $uid . '_' . $f['name']; $req = $f['required']; ?>
      <div class="<?= $f['width'] === 'half' ? '' : 'sm:col-span-2' ?>" data-form-field="<?= View::e($f['name']) ?>">
        <?php if ($f['type'] === 'checkbox'): ?>
          <label class="flex cursor-pointer items-start gap-3 text-sm leading-6">
            <input type="checkbox" name="<?= View::e($f['name']) ?>" value="1" class="mt-1 size-4 shrink-0 accent-primary" <?= $req ? 'required' : '' ?>>
            <span><?= View::e($f['label']) ?><?= $req ? ' <span class="text-destructive">*</span>' : '' ?></span>
          </label>
        <?php elseif ($f['type'] === 'radio'): ?>
          <p class="label"><?= View::e($f['label']) ?><?= $req ? ' <span class="text-destructive">*</span>' : '' ?></p>
          <div class="mt-2 flex flex-wrap gap-x-5 gap-y-2">
            <?php foreach ($f['options'] as $o): ?>
              <label class="flex cursor-pointer items-center gap-2 text-sm"><input type="radio" name="<?= View::e($f['name']) ?>" value="<?= View::e($o) ?>" class="size-4 accent-primary" <?= $req ? 'required' : '' ?>><?= View::e($o) ?></label>
            <?php endforeach ?>
          </div>
        <?php else: ?>
          <label for="<?= $id ?>" class="label"><?= View::e($f['label']) ?><?= $req ? ' <span class="text-destructive">*</span>' : '' ?></label>
          <?php if ($f['type'] === 'textarea'): ?>
            <textarea id="<?= $id ?>" name="<?= View::e($f['name']) ?>" rows="5" maxlength="5000" class="input" placeholder="<?= View::e($f['placeholder']) ?>" <?= $req ? 'required' : '' ?>></textarea>
          <?php elseif ($f['type'] === 'select'): ?>
            <select id="<?= $id ?>" name="<?= View::e($f['name']) ?>" class="input" <?= $req ? 'required' : '' ?>>
              <option value=""><?= View::e($f['placeholder'] ?: 'Seçin') ?></option>
              <?php foreach ($f['options'] as $o): ?><option value="<?= View::e($o) ?>"><?= View::e($o) ?></option><?php endforeach ?>
            </select>
          <?php else: ?>
            <input id="<?= $id ?>" type="<?= $f['type'] ?>" name="<?= View::e($f['name']) ?>" maxlength="300" class="input" placeholder="<?= View::e($f['placeholder']) ?>"
                   <?= $req ? 'required' : '' ?> <?= ['email' => 'autocomplete="email"', 'tel' => 'autocomplete="tel"'][$f['type']] ?? '' ?>>
          <?php endif ?>
        <?php endif ?>
        <?php if ($f['help'] !== ''): ?><p class="field-help text-xs"><?= View::e($f['help']) ?></p><?php endif ?>
      </div>
    <?php endforeach ?>
  </div>

  <button class="btn btn-primary btn-lg mt-6" data-form-submit><?= View::e($form['settings']['button_text']) ?></button>
</form>
<script type="module" src="<?= View::e(Module::asset('Forms', 'form.js')) ?>"></script>
