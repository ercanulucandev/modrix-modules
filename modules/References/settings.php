<?php
return [
    'description' => '/references sayfasının başlığı ve açıklaması. İçerik: Admin > Müşteri yorumları, Admin > Referans logoları.',
    'fields' => [
        'references_title'       => ['label' => 'Sayfa başlığı', 'type' => 'text', 'rules' => 'required|max:100', 'default' => 'Referanslarımız'],
        'references_description' => ['label' => 'Açıklama', 'type' => 'textarea', 'rules' => 'nullable|max:300', 'default' => 'Birlikte çalıştığımız markalar ve müşterilerimizin yorumları.'],
    ],
];
