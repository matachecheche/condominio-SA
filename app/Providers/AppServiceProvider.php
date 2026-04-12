<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */


    
    public function register(): void
    {
        // Aquí puedes registrar servicios si lo necesitas
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Usar Bootstrap 5 para la paginación
        Paginator::useBootstrapFive();

        // Configurar zona horaria SOLO si usas MySQL
        if (config('database.default') === 'mysql') {
            DB::statement("SET time_zone='-04:00'");
        }
    }
}

