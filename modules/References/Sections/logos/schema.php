<?php
return [
    'name'        => 'Referans logoları',
    'description' => 'Admin > Referans logoları sayfasındaki markalar.',
    'icon'        => 'award',
    'fields'      => [
        'title' => ['label' => 'Başlık', 'type' => 'text', 'default' => 'Bize güvenen markalar'],
        'text'  => ['label' => 'Açıklama', 'type' => 'text', 'default' => ''],
        'count' => ['label' => 'En fazla', 'type' => 'select', 'default' => '10', 'options' => ['3' => '3', '5' => '5', '6' => '6', '10' => '10', '50' => 'Hepsi']],
    ],
];
