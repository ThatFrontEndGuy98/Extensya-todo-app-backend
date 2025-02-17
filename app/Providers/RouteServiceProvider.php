<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{

    public function boot(): void
    {
        $this->routes(function () {
            // API Routes
            Route::middleware('api')
                ->prefix('api')
                ->group(function () {
                    require base_path('routes/api.php');
                });

            // Web Routes
            Route::middleware('web')
                ->group(function () {
                    require base_path('routes/web.php');
                });
        });
    }
}