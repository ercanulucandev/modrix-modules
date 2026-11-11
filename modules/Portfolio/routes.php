<?php
use App\Modules\Portfolio\Controllers\PortfolioController;

// Portfolyo: /portfolio, /portfolio/{adres}
Router::get('/portfolio', [PortfolioController::class, 'index'])->name('portfolio.index');
Router::get('/portfolio/{slug}', [PortfolioController::class, 'show'])->name('portfolio.show');
