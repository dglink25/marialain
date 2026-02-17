<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\URL;
use Cloudinary\Configuration\Configuration;
use App\Models\Student;
use App\Models\ParentUser;

class AppServiceProvider extends ServiceProvider{
   
    public function register(): void {
        //
    }


    public function boot(){
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Student::saved(function ($student) {
            if ($student->parent_phone) {
                $parent = ParentUser::updateOrCreate(
                    ['phone' => $student->parent_phone],
                    [
                        'full_name' => $student->parent_full_name ?? 'Parent ' . $student->parent_phone,
                        'email' => $student->parent_email,
                        // Ne pas écraser le mot de passe existant
                    ]
                );
            }
        });
    
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
