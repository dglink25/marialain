<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService{
    
    public static function formatNumber(string $phone): string{
        // Si déjà au format international, on ne touche pas
        if (str_starts_with($phone, '+')) {
            return $phone;
        }

        // "01XXXXXXXX" (10 chiffres) → retire les 2 premiers chars → "+229XXXXXXXX"
        return '+229' . substr($phone, 2);
    }

    public static function send(string $phone, string $message): bool {
        if (! config('whatsapp.enabled')) {
            Log::info('[WhatsApp] Désactivé — message non envoyé', ['to' => $phone]);
            return true;
        }

        $baseUrl  = rtrim(config('whatsapp.gateway_url'), '/');
        $sendPath = config('whatsapp.send_path', '/send');
        $fullUrl  = $baseUrl . $sendPath;

        Log::info('[WhatsApp] Envoi', ['url' => $fullUrl, 'to' => $phone]);

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'x-api-secret' => config('whatsapp.api_secret'),
                    'Content-Type'  => 'application/json',
                ])
                ->post($fullUrl, [
                    'phone'   => $phone,   // paramètre attendu par server.js
                    'message' => $message,
                ]);

            if ($response->successful()) {
                Log::info('[WhatsApp] Envoyé', ['to' => $phone]);
                return true;
            }

            Log::warning('[WhatsApp] Échec', [
                'to'     => $phone,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return false;

        } catch (\Throwable $e) {
            Log::error('[WhatsApp] Exception', [
                'to'      => $phone,
                'message' => $e->getMessage(),
            ]);
            return false;
        }
    }

   
    public static function sendNoteNotification(
        string $parentPhone,
        string $studentName,
        string $className,
        string $subjectName,
        string $type,
        int    $sequence,
        float  $value,
        int    $trimestre,
        string $academicYear
    ): bool {
        $whatsappNumber = self::formatNumber($parentPhone);

        // Libellé lisible du type
        $typeLabel = match($type) {
            'devoir'        => 'Devoir',
            'interrogation' => 'Interrogation',
            default         => ucfirst($type),
        };

        // Note formatée : 14 au lieu de 14.00, mais 14.5 reste 14.5
        $noteFormatted = rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');

        $message = "*CPEG MARIE-ALAIN — {$academicYear}*\n\n"
                 . "Nouvelle note pour *{$studentName}* (*{$className}*)\n"
                 . "Trimestre {$trimestre}\n\n"
                 . " *{$typeLabel} N°{$sequence} {$subjectName}* : *{$noteFormatted}/20*\n\n"
                 . "_Espace parent : " . config('app.url') . "/parent/login_";

        return self::send($whatsappNumber, $message);
    }
}