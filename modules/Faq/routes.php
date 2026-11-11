<?php
// SSS: /faq
use App\Modules\Faq\Controllers\FaqController;

Router::get('/faq', [FaqController::class, 'index'])->name('faq.index');
