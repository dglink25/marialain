<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class InvitationAcceptController extends Controller{
    public function showForm($token){
        $inv = Invitation::where('token',$token)->firstOrFail();
        if ($inv->isExpired()) return view('invitations.expired');
        return view('invitations.accept', compact('inv'));
    }

    public function accept(Request $request, $token){
        $request->validate(['password' => 'required|string|min:8|confirmed']);
        $inv = Invitation::where('token',$token)->firstOrFail();
        if ($inv->isExpired()) return redirect()->route('home')->with('error','Invitation expirée');

        // create or update user
        $user = User::where('email',$inv->email)->first();
        if (! $user) {
            $user = User::create([
                'name' => $inv->email ?? 'Utilisateur',
                'email' => $inv->email,
                'password' => Hash::make($request->password),
            ]);
        } else {
            $user->update(['password'=>Hash::make($request->password)]);
        }

        $user->assignRole($inv->role);
        $inv->update(['accepted'=>true]);

        auth()->login($user);
        return redirect()->route('home')->with('success','Compte activé, bienvenue');
    }
}