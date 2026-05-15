<?php

namespace App\Http\Controllers;

use App\Models\ParentUser;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ParentPhoneVerificationController extends Controller{
    private function generateOtp(): string {
        return str_pad((string) random_int(10000, 99999), 5, '0', STR_PAD_LEFT);
    }

    public function sendOtp(Request $request) {
        /** @var ParentUser $parent */
        $parent = Auth::guard('parent')->user();

        if (! $parent) {
            return response()->json(['success' => false, 'message' => 'Session expirée.'], 401);
        }

        // ── Respect du délai de 60 secondes ──
        if ($parent->phone_otp_sent_at) {
            $secondsElapsed = now()->diffInSeconds($parent->phone_otp_sent_at, false);
            $waitRemaining  = 60 + $secondsElapsed;
            if ($waitRemaining > 0) {
                return response()->json([
                    'success'      => false,
                    'message'      => "Veuillez attendre {$waitRemaining} seconde(s) avant de renvoyer le code.",
                    'wait_seconds' => (int) $waitRemaining,
                ], 429);
            }
        }

        $otp = $this->generateOtp();

        $parent->update([
            'phone_otp'            => $otp,
            'phone_otp_expires_at' => now()->addMinutes(5),
            'phone_otp_sent_at'    => now(),
        ]);

        $whatsappNumber = WhatsAppService::formatNumber($parent->phone);
        $parentName     = explode(' ', trim($parent->full_name))[0];

        $message = "Bonjour {$parentName}\n\n"
                 . "Votre code de vérification CPEG MARIE-ALAIN est :\n\n"
                 . "*{$otp}*\n\n"
                 . "Ce code est valable *5 minutes*.\n"
                 . "Ne le partagez avec personne.";

        $sent = WhatsAppService::send($whatsappNumber, $message);

        if (! $sent) {
            $parent->update(['phone_otp_sent_at' => null]);
            return response()->json([
                'success' => false,
                'message' => "Impossible d'envoyer le message WhatsApp. Réessayez dans un moment.",
            ], 500);
        }

        return response()->json([
            'success'       => true,
            'message'       => "Code envoyé sur WhatsApp au {$whatsappNumber}.",
            'phone_display' => $whatsappNumber,
        ]);
    }

    public function verifyOtp(Request $request) {
        $request->validate([
            'otp' => 'required|string|size:5|regex:/^[0-9]{5}$/',
        ], [
            'otp.required' => 'Veuillez entrer le code reçu.',
            'otp.size'     => 'Le code doit comporter exactement 5 chiffres.',
            'otp.regex'    => 'Le code doit contenir uniquement des chiffres.',
        ]);

        /** @var ParentUser $parent */
        $parent = Auth::guard('parent')->user();

        if (! $parent) {
            return response()->json(['success' => false, 'message' => 'Session expirée.'], 401);
        }

        if (! $parent->phone_otp_expires_at || now()->isAfter($parent->phone_otp_expires_at)) {
            return response()->json([
                'success' => false,
                'message' => 'Ce code a expiré. Veuillez en demander un nouveau.',
                'expired' => true,
            ], 422);
        }

        if ($request->otp !== $parent->phone_otp) {
            return response()->json([
                'success' => false,
                'message' => 'Code incorrect. Vérifiez le message reçu sur WhatsApp.',
            ], 422);
        }

        $parent->update([
            'is_verifie_phone'     => true,
            'verifie_phone_at'     => now(),
            'phone_otp'            => null,
            'phone_otp_expires_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Numéro vérifié avec succès ! Définissez maintenant votre nouveau mot de passe.',
        ]);
    }

    public function changePassword(Request $request) {
        $request->validate([
            'password'              => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required|string',
        ], [
            'password.required'  => 'Veuillez saisir un mot de passe.',
            'password.min'       => 'Le mot de passe doit comporter au moins 8 caractères.',
            'password.confirmed' => 'Les deux mots de passe ne correspondent pas.',
        ]);

        /** @var ParentUser $parent */
        $parent = Auth::guard('parent')->user();

        if (! $parent) {
            return response()->json(['success' => false, 'message' => 'Session expirée.'], 401);
        }

        if (! $parent->is_verifie_phone) {
            return response()->json([
                'success' => false,
                'message' => "Vous devez d'abord vérifier votre numéro de téléphone.",
            ], 403);
        }

        $newPassword = $request->password;

        $parent->update(['password' => Hash::make($newPassword)]);

        $whatsappNumber = WhatsAppService::formatNumber($parent->phone);
        $parentName     = explode(' ', trim($parent->full_name))[0];

        $message = "Mot de passe mis à jour !\n\n"
                 . "Bonjour {$parentName},\n"
                 . "Votre nouveau mot de passe pour votre espace parent CPEG MARIE-ALAIN est :\n\n"
                 . "*{$newPassword}*\n\n"
                 . "Conservez-le précieusement et ne le partagez avec personne.";

        WhatsAppService::send($whatsappNumber, $message);

        return response()->json([
            'success'      => true,
            'message'      => 'Mot de passe mis à jour avec succès !',
            'new_password' => $newPassword,
        ]);
    }
}