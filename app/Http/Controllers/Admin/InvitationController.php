<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvitationRequest;
use App\Models\Invitation;
use App\Models\Year;
use App\Notifications\InvitationNotification;
use App\Services\SmsService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Hash;

class InvitationController extends Controller{
    /**
     * Afficher la liste des invitations
     */
    public function index(){
        $invitations = Invitation::with('creator')->latest()->paginate(20);
        return view('admin.invitations.index', compact('invitations'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $years = Year::all();
        return view('admin.invitations.create', compact('years'));
    }

    /**
     * Enregistrer une nouvelle invitation
     */
    public function store(StoreInvitationRequest $request)
    {
        $data = $request->validated();
        $plain = Str::random(10);

        $inv = Invitation::create([
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'],
            'year_id' => $data['year_id'],
            'token' => (string) Str::uuid(),
            'temporary_password' => Hash::make($plain),
            'expires_at' => now()->addHours(72),
            'created_by' => auth()->id(),
            'accepted' => false,
        ]);

        // 🔹 Envoi email
        if ($inv->email) {
            Notification::route('mail', $inv->email)
                ->notify(new InvitationNotification($inv, $plain));
        }

        // 🔹 Envoi SMS
        if ($inv->phone) {
            try {
                app(SmsService::class)->send(
                    $inv->phone,
                    "Invitation : rôle={$inv->role}, Email={$inv->email}, Pass={$plain}, Lien: " . url("/invitation/accept/{$inv->token}")
                );
            } catch (\Exception $e) {
                \Log::error('SMS error: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.invitations.index')
            ->with('success', 'Invitation envoyée avec succès.');
    }

    /**
     * Supprimer une invitation
     */
    public function destroy(Invitation $invitation)
    {
        $invitation->delete();
        return redirect()->back()->with('success', 'Invitation révoquée.');
    }

    /**
     * Renvoyer une invitation
     */
    public function resend(Invitation $invitation)
    {
        $plain = Str::random(10);

        $invitation->update([
            'temporary_password' => Hash::make($plain),
            'expires_at' => now()->addHours(72),
            'accepted' => false,
        ]);

        if ($invitation->email) {
            Notification::route('mail', $invitation->email)
                ->notify(new InvitationNotification($invitation, $plain));
        }

        if ($invitation->phone) {
            try {
                app(SmsService::class)->send(
                    $invitation->phone,
                    "Nouvelle invitation : rôle={$invitation->role}, Pass={$plain}, Lien: " . url("/invitation/accept/{$invitation->token}")
                );
            } catch (\Exception $e) {
                \Log::error('SMS error: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', 'Invitation renvoyée.');
    }
}
