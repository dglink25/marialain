<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Role;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // Exemple :
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // 🔑 Exemple Gate pour restreindre par rôle
        Gate::define('is-censeur', function (User $user) {
            return $user->role && $user->role->name === 'censeur';
        });

        Gate::define('is-directeur-primaire', function (User $user) {
            return $user->role && $user->role->name === 'directeur primaire';
        });

        Gate::define('is-surveillant', function (User $user) {
            return $user->role && $user->role->name === 'surveillant';
        });

        Gate::define('is-secretaire', function (User $user) {
            return $user->role && $user->role->name === 'secretaire';
        });

        Gate::define('is-admin', function (User $user) {
            return $user->role && $user->role->name === 'admin';
        });
    }
}
