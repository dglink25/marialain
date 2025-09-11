<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RedirectIfRole {
    public function handle($request, Closure $next) {
        if (Auth::check()) {
            $role = Auth::user()->role->name ?? null;
            switch ($role) {
                case 'directeur_primaire': return redirect()->route('directeur.dashboard');
                case 'censeur': return redirect()->route('censeur.dashboard');
                case 'surveillant': return redirect()->route('surveillant.dashboard');
                case 'secretaire': return redirect()->route('secretaire.dashboard');
                default: return redirect()->route('admin.dashboard');
            }
        }
        return $next($request);
    }
}