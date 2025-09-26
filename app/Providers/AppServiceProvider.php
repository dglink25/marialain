<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public function boot(): void{
        $activeYear = AcademicYear::where('active', true)->first();
        View::share('activeYear', $activeYear);
    }

    
}
