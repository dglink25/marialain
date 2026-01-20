<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleExpiredSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Vérifier si la réponse est une erreur 419 (Session expirée)
        if ($response->status() === 419) {
            // Rediriger vers la page de login avec un message d'erreur
            return redirect()->route('login')
                ->with('error', 'Votre session a expiré. Veuillez vous reconnecter.')
                ->with('csrf_error', 'Session expirée en raison d\'une inactivité prolongée.');
        }
        
        return $response;
    }
    
}