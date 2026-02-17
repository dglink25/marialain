<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;
use Cloudinary\Configuration\Configuration;

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
         $cloudName = config('cloudinary.cloud_name');
        $apiKey = config('cloudinary.api_key');
        $apiSecret = config('cloudinary.api_secret');
        $cloudUrl = config('cloudinary.cloud_url');

        try {
            if ($cloudUrl) {
                Configuration::instance($cloudUrl);
            } elseif ($cloudName && $apiKey && $apiSecret) {
                Configuration::instance([
                    'cloud' => [
                        'cloud_name' => $cloudName,
                        'api_key' => $apiKey,
                        'api_secret' => $apiSecret
                    ]
                ]);
            }
        } 
        catch (\Exception $e) {
            //Log::error('Erreur configuration Cloudinary: ' . $e->getMessage());
        }
    }

    
}
