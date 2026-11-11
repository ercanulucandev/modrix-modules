<?php
return [
    'description' => '/portfolio sayfasının başlığı ve açıklaması. Projeler: Admin > Portfolyo.',
    'fields' => [
        'portfolio_title'       => ['label' => 'Sayfa başlığı', 'type' => 'text', 'rules' => 'required|max:100', 'default' => 'Projelerimiz'],
        'portfolio_description' => ['label' => 'Açıklama', 'type' => 'textarea', 'rules' => 'nullable|max:300', 'default' => 'Tamamladığımız işlerden seçmeler.'],
    ],
];
