<?php
return [
    'name'        => 'Ekibimiz',
    'description' => 'Admin > Ekip\'teki üyeler, fotoğraflı kartlar.',
    'icon'        => 'users',
    'fields'      => [
        'title'   => ['label' => 'Başlık', 'type' => 'text', 'default' => 'Ekibimiz'],
        'text'    => ['label' => 'Açıklama', 'type' => 'text', 'default' => 'İşinizi büyütmek için birlikte çalışan ekip.'],
        'columns' => ['label' => 'Sütun', 'type' => 'select', 'default' => '4', 'options' => ['3' => '3', '4' => '4']],
        'count'   => ['label' => 'En fazla', 'type' => 'select', 'default' => '8', 'options' => ['3' => '3', '4' => '4', '6' => '6', '8' => '8', '50' => 'Hepsi']],
    ],
];
