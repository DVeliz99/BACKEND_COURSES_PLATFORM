<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class JwtAuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        require_once app_path().'/Helpers/jwtAuth.php'; //añadir el helper al servicio 
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
