<?php
// Formlar bölümü: seçilen form, yanında iletişim bilgileriyle (her temada)
$forms = [];
try {
    $forms = array_map('strval', App\Modules\Forms\Models\Form::options());
} catch (Throwable) {
}

return [
    'name'        => 'Form',
    'description' => 'İletişim ya da başvuru formu; yanında iletişim bilgileri.',
    'icon'        => 'mail',
    'requires'    => 'Forms',
    'fields'      => [
        'form'         => ['label' => 'Form', 'type' => 'select', 'default' => (string) array_key_first($forms), 'options' => $forms,
                           'help' => 'Formlar Admin > Formlar\'da oluşturulur ve düzenlenir.'],
        'anchor'       => ['label' => 'Bölüm adresi (#)', 'type' => 'text', 'default' => 'iletisim'],
        'title'        => ['label' => 'Başlık', 'type' => 'text', 'default' => 'Bize yazın'],
        'text'         => ['label' => 'Açıklama', 'type' => 'textarea', 'default' => 'Formu doldurun, en kısa sürede size dönüş yapalım.'],
        'show_contact' => ['label' => 'Yanında iletişim bilgileri', 'type' => 'checkbox', 'default' => true],
        'background'   => ['label' => 'Zemin', 'type' => 'select', 'default' => 'none', 'options' => ['none' => 'Yok', 'muted' => 'Açık gri']],
    ],
];
