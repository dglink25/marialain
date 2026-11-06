<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogger
{
    /**
     * Enregistrer une action dans les logs.
     */
    public static function log(
        string  $action,
        string  $description,
        ?string $model    = null,
        ?int    $modelId  = null,
        ?array  $oldData  = null,
        ?array  $newData  = null,
        ?Request $request = null
    ): void {
        try {
            $user    = auth()->user();
            $request = $request ?? request();

            $userAgent = $request->userAgent() ?? '';
            $device    = self::detectDevice($userAgent);

            ActivityLog::create([
                'user_id'     => $user?->id,
                'user_name'   => $user?->name ?? 'Anonyme',
                'user_role'   => optional($user?->role)->name ?? 'inconnu',
                'action'      => $action,
                'model'       => $model,
                'model_id'    => $modelId,
                'description' => $description,
                'old_data'    => $oldData,
                'new_data'    => $newData,
                'ip_address'  => $request->ip(),
                'user_agent'  => substr($userAgent, 0, 500),
                'device'      => $device,
                'url'         => substr($request->fullUrl(), 0, 500),
                'method'      => $request->method(),
            ]);
        } catch (\Throwable $e) {
            // Ne jamais faire crasher l'app à cause des logs
            \Illuminate\Support\Facades\Log::error('ActivityLogger failed: ' . $e->getMessage());
        }
    }

    private static function detectDevice(string $userAgent): string
    {
        $ua = strtolower($userAgent);
        if (str_contains($ua, 'mobile') || str_contains($ua, 'android') || str_contains($ua, 'iphone')) {
            return 'Mobile';
        }
        if (str_contains($ua, 'tablet') || str_contains($ua, 'ipad')) {
            return 'Tablet';
        }
        return 'Desktop';
    }

    // ── Helpers sémantiques ──────────────────────────────────────────

    public static function logCreate(string $description, string $model, int $modelId, array $data = []): void
    {
        self::log('create', $description, $model, $modelId, null, $data);
    }

    public static function logUpdate(string $description, string $model, int $modelId, array $old, array $new): void
    {
        self::log('update', $description, $model, $modelId, $old, $new);
    }

    public static function logDelete(string $description, string $model, int $modelId, array $oldData = []): void
    {
        self::log('delete', $description, $model, $modelId, $oldData, null);
    }

    public static function logLogin(string $description): void
    {
        self::log('login', $description, 'User', auth()->id());
    }

    public static function logLogout(string $description): void
    {
        self::log('logout', $description, 'User', auth()->id());
    }
}
