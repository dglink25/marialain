<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\ActivityLogger;
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
    public function store(LoginRequest $request): RedirectResponse  {
        try {
            $request->authenticate();
            $request->session()->regenerate();

            ActivityLogger::log('login', 'Connexion réussie', 'User', auth()->id());

            return redirect()->intended(route('admin.accueil'));

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Log tentative échouée
            ActivityLogger::log('login_failed', 'Tentative de connexion échouée pour : ' . $request->email, null, null);
            return back()
                ->withErrors(['email' => 'Les identifiants fournis ne correspondent pas à nos enregistrements.'])
                ->withInput($request->only('email', 'remember'));
        }
        catch (\Exception $e) {
            return back()
                ->withErrors(['auth' => 'Identifiants incorrects'])
                ->withInput($request->only('email', 'remember'));
        }
    }

    public function destroy(Request $request): RedirectResponse {
        ActivityLogger::log('logout', 'Déconnexion', 'User', auth()->id());

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/home');
    }
}