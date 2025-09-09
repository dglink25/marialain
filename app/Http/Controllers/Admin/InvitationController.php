<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvitationRequest;
use App\Models\Invitation;
use App\Notifications\InvitationNotification;
use App\Services\SmsService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Hash;

class InvitationController extends Controller{
    public function index()
    {
        $invitations = Invitation::with('creator')->latest()->paginate(20);
        return view('admin.invitations.index', compact('invitations'));
    }

    public function store(StoreInvitationRequest $request)
    {
        $data = $request->validated();
        $plain = Str::random(10);

        $inv = Invitation::create([
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'],
            'token' => (string) Str::uuid(),
            'temporary_password' => Hash::make($plain),
            'expires_at' => now()->addHours(72),
            'created_by' => auth()->id(),
        ]);

        // Envoyer email
        if ($inv->email) {
            Notification::route('mail', $inv->email)
                ->notify(new InvitationNotification($inv, $plain));
        }

        // Envoyer SMS
        if ($inv->phone) {
            try {
                app(SmsService::class)->send(
                    $inv->phone,
                    "Invitation MARIE ALAIN: role={$inv->role}. Email={$inv->email}. Pass: {$plain}. Lien: " . url("/invitation/accept/{$inv->token}")
                );
            } catch (\Exception $e) {
                \Log::error('SMS error: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', 'Invitation envoyée');
    }

    public function destroy(Invitation $invitation)
    {
        $invitation->delete();
        return redirect()->back()->with('success', 'Invitation révoquée');
    }

    public function resend(Invitation $invitation)
    {
        $plain = Str::random(10);

        $invitation->update([
            'temporary_password' => Hash::make($plain),
            'expires_at' => now()->addHours(72),
            'accepted' => false,
        ]);

        // Envoyer email
        if ($invitation->email) {
            Notification::route('mail', $invitation->email)
                ->notify(new InvitationNotification($invitation, $plain));
        }

        // Envoyer SMS
        if ($invitation->phone) {
            try {
                app(SmsService::class)->send(
                    $invitation->phone,
                    "Invitation MARIE ALAIN: role={$invitation->role}. Pass: {$plain}. Lien: " . url("/invitation/accept/{$invitation->token}")
                );
            } catch (\Exception $e) {
                \Log::error('SMS error: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', 'Invitation renvoyée');
    }
}