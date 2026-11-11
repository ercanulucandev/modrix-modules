<?php
return [
    'name'        => 'Randevu al',
    'description' => 'Randevu formu ya da randevu sayfasına giden bant.',
    'icon'        => 'calendar',
    'fields'      => [
        'title'  => ['label' => 'Başlık', 'type' => 'text', 'default' => 'Hemen randevu alın'],
        'text'   => ['label' => 'Açıklama', 'type' => 'text', 'default' => 'Size uygun gün ve saati seçin, birkaç saniyede randevunuz hazır.'],
        'style'  => ['label' => 'Görünüm', 'type' => 'select', 'default' => 'form', 'options' => ['form' => 'Form sayfada', 'button' => 'Sadece düğme']],
        'button' => ['label' => 'Düğme yazısı', 'type' => 'text', 'default' => 'Randevu al'],
    ],
];
