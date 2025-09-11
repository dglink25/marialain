<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller{
    public function edit() {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request) {
        $user = Auth::user();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:20',
        ]);
        $user->update($request->only('name','email','phone'));
        return back()->with('success','Profil mis à jour.');
    }

    public function updatePassword(Request $request) {
        $request->validate([
            'password' => 'required|confirmed|min:8'
        ]);
        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->save();
        return back()->with('success','Mot de passe modifié.');
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
