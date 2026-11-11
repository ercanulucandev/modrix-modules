<?php
return [
    'name'        => 'Müşteri yorumları',
    'description' => 'Admin > Müşteri yorumları sayfasındaki yorumlar.',
    'icon'        => 'quote',
    'fields'      => [
        'title' => ['label' => 'Başlık', 'type' => 'text', 'default' => 'Müşterilerimiz ne diyor?'],
        'text'  => ['label' => 'Açıklama', 'type' => 'text', 'default' => 'Birlikte çalıştığımız müşterilerimizin yorumları.'],
        'count' => ['label' => 'En fazla', 'type' => 'select', 'default' => '3', 'options' => ['3' => '3', '5' => '5', '6' => '6', '10' => '10', '50' => 'Hepsi']],
    ],
];
