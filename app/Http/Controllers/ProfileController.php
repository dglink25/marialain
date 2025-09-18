<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Smalot\PdfParser\Parser;

class ProfileController extends Controller{
    public function edit() {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    public function updates(Request $request) {
        $user = Auth::user();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:20',
        ]);
        $user->update($request->only('name','email','phone'));
        return back()->with('success','Profil mis à jour.');
    }


    public function show()
    {
        $user = auth()->user();
        return view('profile.show', compact('user'));
    }


    public function updatePassword(Request $request){
        $request->validate([
            'old_password' => 'required',
            'password' => 'required|confirmed|min:8',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->old_password, $user->password)) {
            return back()->withErrors(['old_password' => 'L’ancien mot de passe est incorrect.'])
                        ->with('showPasswordForm', true);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Mot de passe modifié avec succès.')
                    ->with('showPasswordForm', true);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'nullable|string',
            'gender' => 'nullable|string',
            'marital_status' => 'nullable|string',
            'address' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'birth_place' => 'nullable|string',
            'nationality' => 'nullable|string',
            'profile_photo' => 'nullable|image|max:2048',

            'id_card_file' => 'nullable|mimes:pdf|max:2048',
            'birth_certificate_file' => 'nullable|mimes:pdf|max:2048',
            'diploma_file' => 'nullable|mimes:pdf|max:2048',
            'ifu_file' => 'nullable|mimes:pdf|max:2048',
            'rib_file' => 'nullable|mimes:pdf|max:2048',
        ]);

        // Upload fichiers + lecture PDF
        $parser = new Parser();

        foreach (['id_card_file','birth_certificate_file','diploma_file','ifu_file','rib_file'] as $field) {
            if ($request->hasFile($field)) {
                $path = $request->file($field)->store('enseignants','public');
                $data[$field] = $path;

                // extraire texte du pdf
                $pdf = $parser->parseFile($request->file($field)->getPathName());
                $text = $pdf->getText();

                if ($field === 'ifu_file') {
                    if (preg_match('/\b\d{13}\b/', $text, $matches)) {
                        $data['ifu_number'] = $matches[0];
                    }
                }
                if ($field === 'id_card_file') {
                    if (preg_match('/[A-Z0-9]{6,}/', $text, $matches)) {
                        $data['id_card_number'] = $matches[0];
                    }
                }
            }
        }

        // Sauvegarder
        $user->update($data);

        return redirect()->route('profile.show')
            ->with('success','Profil mis à jour avec succès.');
    }


    public function updatePhoto(Request $request) {
        $user = Auth::user();
        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profiles','public');
            $user->profile_photo = $path;
            $user->save();
        }
        if ($request->get('remove_photo')) {
            $user->profile_photo = null;
            $user->save();
        }
        return back()->with('success','Photo mise à jour.');
    }
}
