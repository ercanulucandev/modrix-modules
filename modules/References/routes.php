<?php
use App\Services\Collection;

// Referanslar: /references
Router::get('/references', fn() => View::render('References::index', [
    'title'           => Setting::get('references_title', 'Referanslarımız'),
    'metaDescription' => Setting::get('references_description'),
    'clients'         => Collection::items('clients'),
    'testimonials'    => Collection::items('testimonials'),
])->setLayout('site'))->name('references.index');
