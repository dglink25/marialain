<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\ActivityLogger;
use Symfony\Component\HttpFoundation\Response;

class LogActivity
{
    // Actions à logger automatiquement (POST/PUT/PATCH/DELETE uniquement)
    private const IGNORE_URLS = [
        'livewire',
        '_debugbar',
        'telescope',
        'horizon',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Logger seulement les mutations (pas les GET)
        if (auth()->check() && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $url = $request->path();

            // Ignorer les URLs internes
            foreach (self::IGNORE_URLS as $ignore) {
                if (str_contains($url, $ignore)) {
                    return $response;
                }
            }

            $method = $request->method();
            $action = match($method) {
                'POST'   => 'create',
                'PUT', 'PATCH' => 'update',
                'DELETE' => 'delete',
                default  => strtolower($method),
            };

            // Construire une description lisible
            $description = self::buildDescription($request, $method, $response->getStatusCode());

            ActivityLogger::log(
                $action,
                $description,
                null,
                null,
                null,
                null,
                $request
            );
        }

        return $response;
    }

    private static function buildDescription(Request $request, string $method, int $statusCode): string
    {
        $path   = $request->path();
        $status = $statusCode >= 400 ? " [ERREUR $statusCode]" : '';

        return match($method) {
            'POST'   => "Création via /$path$status",
            'PUT'    => "Modification via /$path$status",
            'PATCH'  => "Mise à jour via /$path$status",
            'DELETE' => "Suppression via /$path$status",
            default  => "$method /$path$status",
        };
    }
}
