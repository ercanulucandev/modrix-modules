<?php
return [
    'description' => '/team sayfasının başlığı ve açıklaması. Üyeler: Admin > Ekip.',
    'fields' => [
        'team_title'       => ['label' => 'Sayfa başlığı', 'type' => 'text', 'rules' => 'required|max:100', 'default' => 'Ekibimiz'],
        'team_description' => ['label' => 'Açıklama', 'type' => 'textarea', 'rules' => 'nullable|max:300', 'default' => 'İşinizi büyütmek için birlikte çalışan ekip.'],
    ],
];
