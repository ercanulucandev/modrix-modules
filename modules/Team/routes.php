<?php
use App\Services\Collection;

// Ekip: /team
Router::get('/team', fn() => View::render('Team::index', [
    'title'           => Setting::get('team_title', 'Ekibimiz'),
    'metaDescription' => Setting::get('team_description'),
    'items'           => Collection::items('team'),
])->setLayout('site'))->name('team.index');
