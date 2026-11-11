<?php
namespace App\Modules\Blog\Middlewares;

use Core\Middleware;

// Modüle özel middleware örneği. app/Config/app.php'ye eklemeden sınıf adıyla kullanılır:
//   Router::group(['middleware' => [NoCacheMiddleware::class]], ...)
class NoCacheMiddleware extends Middleware
{
    public function handle(): void
    {
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
    }
}
