<?php
// Form bölümü. $s: form, anchor, title, text, show_contact, background
use App\Modules\Forms\Models\Form;

$form = Form::get((int) $s['form']);
if (!$form) return;
$contact = $s['show_contact'] ? array_filter([
    ['phone', 'Telefon', Setting::get('contact_phone')],
    ['mail', 'E-posta', Setting::get('contact_email')],
    ['pin', 'Adres', Setting::get('contact_address')],
], fn($c) => $c[2]) : [];
?>
<section <?= $s['anchor'] !== '' ? 'id="' . View::e($s['anchor']) . '"' : '' ?> class="scroll-mt-20 py-14 sm:py-20 <?= $s['background'] === 'muted' ? 'bg-muted' : '' ?>">
  <div class="wrap grid gap-10 <?= $contact ? 'lg:grid-cols-[2fr_3fr]' : 'max-w-3xl' ?>">
    <div>
      <h2 class="text-2xl font-semibold tracking-tight sm:text-3xl" <?= $edit('title') ?>><?= View::e($s['title']) ?></h2>
      <?php if ($s['text'] !== ''): ?><p class="mt-3 leading-7 text-muted-foreground" <?= $edit('text') ?>><?= nl2br(View::e($s['text'])) ?></p><?php endif ?>
      <?php if ($contact): ?>
        <ul class="mt-8 space-y-4">
          <?php foreach ($contact as [$icon, $label, $value]): ?>
            <li class="flex gap-3">
              <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary"><?= Icon::svg($icon, 'size-5') ?></span>
              <span><span class="block text-xs text-muted-foreground"><?= $label ?></span><span class="block break-words font-medium"><?= nl2br(View::e($value)) ?></span></span>
            </li>
          <?php endforeach ?>
        </ul>
      <?php endif ?>
    </div>
    <div class="card p-6 sm:p-8" <?= $edit('form') ?>><?= View::render('Forms::form', ['form' => $form]) ?></div>
  </div>
</section>
