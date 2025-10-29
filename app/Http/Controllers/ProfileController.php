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
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Cloudinary\Api\Upload\UploadApi;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ProfileController extends Controller{
    public function edit() {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    public function show($id){
        $user = User::findOrFail($id);

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

    public function update(Request $request){
        $user = auth()->user();

        $data = $request->validate([
            'name' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'gender' => 'nullable|string',
            'marital_status' => 'nullable|string',
            'address' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'birth_place' => 'nullable|string',
            'nationality' => 'nullable|string',
            'profile_photo' => 'nullable|image|max:2048',

            'id_card_file' => 'nullable|file|max:2048',
            'birth_certificate_file' => 'nullable|file|max:2048',
            'diploma_file' => 'nullable|file|max:2048',
            'ifu_file' => 'nullable|file|max:2048',
            'rib_file' => 'nullable|file|max:2048',
        ]);

        // 1️⃣ Photo de profil sur Cloudinary
        if ($request->hasFile('profile_photo')) {
            // Supprimer l’ancienne si elle existe
            if ($user->profile_photo) {
                (new UploadApi())->destroy($user->profile_photo);
            }

            $uploaded = Cloudinary::upload($request->file('profile_photo')->getRealPath(), [
                'folder' => 'profiles',
            ]);

            $data['profile_photo'] = $uploaded['secure_url'];
            $data['profile_photo'] = $uploaded['public_id'];
        }

        $parser = new \Smalot\PdfParser\Parser();

        // 2️⃣ Autres fichiers sur Cloudinary
        foreach (['id_card_file','birth_certificate_file','diploma_file','ifu_file','rib_file'] as $field) {
            if ($request->hasFile($field)) {

                $uploadApi = new UploadApi();
                $uploaded = $uploadApi->upload(
                    $request->file($field)->getRealPath(), [
                        'folder' => 'enseignants',
                        'resource_type' => 'auto', // 👈 IMPORTANT : gère PDF, images, vidéos, etc.
                    ]
                );

                $data[$field] = $uploaded['secure_url'];

                // Extraction texte si PDF
                //$pdf = $parser->parseFile($request->file($field)->getPathName());
                //$text = $pdf->getText();
            }
        }

        $user->update(array_filter($data, fn($value) => $value !== null));

        return back()->with('success','Profil mis à jour avec succès.');
    }


    public function updatePhoto(Request $request) {
        $user = Auth::user();

        // Mettre à jour la photo si un fichier est envoyé
        if ($request->hasFile('profile_photo')) {
            // Supprimer l'ancienne photo sur Cloudinary si elle existe
            if ($user->profile_photo) {
                try {
                    $uploadApi = new UploadApi();
                    $uploadApi->destroy($user->profile_photo);
                } catch (\Exception $e) {
                    \Log::error('Erreur suppression photo Cloudinary : ' . $e->getMessage());
                }
            }

            // Upload sur Cloudinary
            $file = $request->file('profile_photo');
            $uploadApi = new UploadApi();
            $uploaded = $uploadApi->upload($file->getRealPath(), [
                'folder' => 'profiles',   // dossier Cloudinary
                'overwrite' => true,
                'resource_type' => 'image',
            ]);

            // Enregistrer l'URL et le public_id pour suppression future
            $user->profile_photo = $uploaded['secure_url'];
            $user->profile_photo = $uploaded['public_id'];
            $user->save();

            return back()->with('success', 'Photo mise à jour avec succès.');
        }

        // Supprimer la photo si le bouton est cliqué
        if ($request->get('remove_photo')) {
            if ($user->profile_photo) {
                try {
                    $uploadApi = new UploadApi();
                    $uploadApi->destroy($user->profile_photo);
                } catch (\Exception $e) {
                    \Log::error('Erreur suppression photo Cloudinary : ' . $e->getMessage());
                }
            }
            $user->profile_photo = null;
            $user->profile_photo = null;
            $user->save();

            return back()->with('success', 'Photo supprimée avec succès.');
        }

        return back()->with('info', 'Aucune modification effectuée.');
    }


}
