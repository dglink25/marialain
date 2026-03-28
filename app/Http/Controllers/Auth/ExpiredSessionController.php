<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExpiredSessionController extends Controller
{
    /**
     * Afficher la page d'erreur de session expirée
     */
    public function show()
    {
        return view('auth.session-expired');
    }

    /**
     * Régénérer le token CSRF
     */
    public function refreshCsrf(Request $request)
    {
        return response()->json([
            'csrf_token' => csrf_token()
        ]);
    }
}