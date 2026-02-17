<?php

namespace App\Http\Controllers;

use App\Models\ParentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ParentAuthController extends Controller{

    public function showLoginForm(){
        return view('auth.parent-login');
    }

    public function login(Request $request) {
        $request->validate([
            'phone' => 'required|string|regex:/^01[0-9]{8}$/',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('phone', 'password');
        
        // Vérifier si le parent existe
        $parent = ParentUser::where('phone', $request->phone)->first();
        
        if (!$parent) {
            return back()
                ->withErrors(['phone' => 'Ce numéro de téléphone n\'est pas reconnu.'])
                ->withInput($request->only('phone'));
        }

        // Vérifier si le parent est actif
        if (!$parent->is_active) {
            return back()
                ->withErrors(['phone' => 'Votre compte a été désactivé. Contactez l\'administration.'])
                ->withInput($request->only('phone'));
        }

        // Vérifier le mot de passe
        if (!Hash::check($request->password, $parent->password)) {
            return back()
                ->withErrors(['password' => 'Mot de passe incorrect.'])
                ->withInput($request->only('phone'));
        }

        // Connecter le parent
        Auth::guard('parent')->login($parent);
        
        // Mettre à jour la dernière connexion
        $parent->update(['last_login_at' => now()]);

        return redirect()->intended(route('parent.dashboard'));
    }

    public function logout(Request $request){
        Auth::guard('parent')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}