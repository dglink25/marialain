<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        try {
            $request->authenticate();
            
            $request->session()->regenerate();
            
            return redirect()->intended(route('admin.accueil'));
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Pour les erreurs de validation (mauvais identifiants)
            return back()
                ->withErrors(['email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.'])
                ->withInput($request->only('email', 'remember'));
        } 
        catch (\Exception $e) {
            // Pour toutes les autres erreurs
            return back()
                ->withErrors(['auth' => 'Identifiants incorrects'])
                ->withInput($request->only('email', 'remember'));
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/home');
    }
}