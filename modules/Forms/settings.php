<?php
// Modüller > Formlar > Ayarlar
return [
    'description' => 'Form bildirimlerinin gideceği adres. Gönderim ayarları: Ayarlar > E-posta.',
    'fields'      => [
        'forms_notify_email' => ['label' => 'Bildirim adresi', 'type' => 'email', 'rules' => 'nullable|email|max:190',
                                 'help' => 'Formun kendi ayarında adres yoksa buraya gönderilir. Boşsa Ayarlar > İletişim\'deki e-posta.'],
    ],
];
