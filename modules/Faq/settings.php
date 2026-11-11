<?php
// Modüller > SSS > Ayarlar
return [
    'description' => '/faq sayfasının başlığı ve açıklaması. Sorular: Admin > SSS.',
    'fields' => [
        'faq_title'       => ['label' => 'Sayfa başlığı', 'type' => 'text', 'rules' => 'required|max:100', 'default' => 'Sıkça sorulan sorular'],
        'faq_description' => ['label' => 'Açıklama', 'type' => 'textarea', 'rules' => 'nullable|max:300', 'default' => 'Merak ettiklerinizin cevapları. Bulamadığınızı bize sorun.'],
    ],
];
