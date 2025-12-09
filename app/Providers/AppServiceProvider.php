<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Model\Evaluacion;
use App\Observers\EvaluacionObserver;

class AppServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Evaluacion::observe(EvaluacionObserver::class);
    }
}
