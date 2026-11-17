<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Athlete;

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
        // Register DomPDF alias
        if (class_exists('Barryvdh\DomPDF\Facade\Pdf')) {
            $this->app->alias('Barryvdh\DomPDF\Facade\Pdf', 'Pdf');
        }

        // Register Excel alias
        if (class_exists('Maatwebsite\Excel\Facades\Excel')) {
            $this->app->alias('Maatwebsite\Excel\Facades\Excel', 'Excel');
        }

        View::composer('layouts.app', function ($view) {
            $view->with('totalAthletes', Athlete::count());
        });
    }
}
