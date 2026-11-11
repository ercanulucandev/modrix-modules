<?php
return [
    'name'        => 'Portfolyo',
    'description' => 'Admin > Portfolyo\'daki son projeler, kapak görselleriyle.',
    'icon'        => 'image',
    'fields'      => [
        'title'     => ['label' => 'Başlık', 'type' => 'text', 'default' => 'Projelerimiz'],
        'text'      => ['label' => 'Açıklama', 'type' => 'text', 'default' => 'Tamamladığımız işlerden seçmeler.'],
        'count'     => ['label' => 'Proje sayısı', 'type' => 'select', 'default' => '6', 'options' => ['3' => '3', '6' => '6', '9' => '9']],
        'link_text' => ['label' => 'Tümü bağlantısı', 'type' => 'text', 'default' => 'Tüm projeler', 'help' => 'Boşsa gösterilmez.'],
    ],
];
