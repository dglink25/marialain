<?php

namespace App\Http\Controllers\Censeur;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\TeacherInvitation;
use App\Mail\TeacherInvitationMail;

class InvitationController extends Controller{
    
    public function index() {
        $invitations = TeacherInvitation::with('user')->latest()->get();
        return view('censeur.invitations.index', compact('invitations'));
    }

    public function send(Request $request) {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
        ]);

        $plainPassword = Str::random(8);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($plainPassword),
            'role_id' => 6,
        ]);

        $invitation = TeacherInvitation::create([
            'user_id' => $user->id,
            'name'  => $request->name,   
            'email' => $request->email, 
            'token' => Str::random(32),
            'censeur_id' => Auth::id(),
        ]);

        Mail::to($user->email)->send(new TeacherInvitationMail($invitation, $plainPassword));

        return back()->with('success', 'Invitation envoyée à '.$user->email);
    }

    public function accept($token) {
        $invitation = TeacherInvitation::where('token', $token)->firstOrFail();
        $invitation->accepted = true;
        $invitation->save();

        return redirect('/login')->with('success', 'Votre compte est activé, veuillez vous connecter.');
    }
}
