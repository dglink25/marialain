<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }


    public function boot(){
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }

    
}
