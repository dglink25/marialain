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
    public function index(){
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
            'created_by' => auth()->id()
        ]);

        // send mail
        if ($inv->email) {
            Notification::route('mail', $inv->email)->notify(new InvitationNotification($inv,$plain));
        }

        // send sms
        if ($inv->phone) {
            try {
                app(SmsService::class)->send($inv->phone, "Invitation MARIE ALAIN: role={$inv->role}. Email={$inv->email}. Passe: {$plain}. Lien: " . url("/invitation/accept/{$inv->token}"));
            } catch (\Exception $e) {
                // log but don't break
                \Log::error('SMS error: '.$e->getMessage());
            }
        }

        return redirect()->back()->with('success','Invitation envoyée');
    }

    public function destroy(Invitation $invitation)
    {
        $invitation->delete();
        return redirect()->back()->with('success','Invitation révoquée');
    }

    public function resend(Invitation $invitation)
    {
        // regenerate temporary password and new expiry
        $plain = Str::random(10);
        $invitation->update(['temporary_password'=>Hash::make($plain),'expires_at'=>now()->addHours(72),'accepted'=>false]);
        if ($invitation->email) Notification::route('mail',$invitation->email)->notify(new InvitationNotification($invitation,$plain));
        if ($invitation->phone) app(SmsService::class)->send($invitation->phone, "Invitation MARIE ALAIN: role={$invitation->role}. Pass: {$plain}. Lien:".url("/invitation/accept/{$invitation->token}"));
        return redirect()->back()->with('success','Invitation renvoyée');
    }
}