<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Mail;

class ContactController extends Controller{
    public function send(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email',
            'message' => 'required|string|max:100000',
        ]);

        try {
            Mail::raw("Message de : {$request->name}\nEmail : {$request->email}\n\n{$request->message}", function ($mail) {
                $mail->to('cpegmariealain@gmail.com')
                     ->subject('Nouveau message du formulaire de contact');
            });

            return response()->json(['success' => true, 'message' => 'Message envoyé avec succès !']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Échec de l’envoi. Réessayez plus tard.']);
        }
    }
}
