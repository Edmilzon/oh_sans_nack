<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Model\Evaluacion;
use App\Observers\EvaluacionObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Aquí registramos el observador
        Evaluacion::observe(EvaluacionObserver::class);
    }
}
