<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TeacherInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class InvitationResponseController extends Controller{
    public function accept($token){
        // On cherche l’invitation
        $invitation = TeacherInvitation::where('token', $token)->first();

        if (!$invitation) {
            return redirect('/')->with('error', 'Lien invalide ou expiré.');
        }

        // Vérifier l’utilisateur lié à l’invitation
        $user = User::find($invitation->user_id);

        if (!$user) {
            abort(400, "L'invitation est invalide (aucun utilisateur associé).");
        }

        // Marquer l’invitation comme acceptée
        
        $invitation->forceFill([
            'accepted' => true,
            'accepted_at' => now(),
        ])->save();
        
        //return redirect()->route('profile.edit');
        
        return redirect('/login')->with('success', 'Invitation acceptée, vous pouvez vous connecter avec vos identifiants.');
    }
}