<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Twilio\Rest\Client as TwilioClient;
use Illuminate\Bus\Queueable;
use App\Models\Invitation;


class InvitationNotification extends Notification{

    use Queueable;
    protected $invitation;
    protected $plainPassword;

    public function __construct(Invitation $invitation, string $plainPassword){
        $this->invitation = $invitation;
        $this->plainPassword = $plainPassword;
    }

    public function via($notifiable){
        $channels = ['mail'];
        //if ($this->invitation->phone) $channels[] = 'twilio'; 
        return $channels;
    }

    public function toMail($notifiable){
        $acceptUrl = url("/invitation/accept/{$this->invitation->token}");
        return (new MailMessage)
            ->subject("Invitation MARIE ALAIN — rôle: {$this->invitation->role}")
            ->line("Vous êtes invité(e) comme {$this->invitation->role} sur MARIE ALAIN.")
            ->line("Email: {$this->invitation->email}")
            ->line("Mot de passe temporaire: {$this->plainPassword} (valable 72h)")
            ->action('Accepter et se connecter', $acceptUrl)
            ->line('L\'invitation expirera le ' . $this->invitation->expires_at->toDateTimeString());
    }

    // Example: custom toTwilio (we'll implement via toArray and a custom notification channel)
    public function toArray($notifiable){
        return [
            'message' => "Invitation MARIE ALAIN: role={$this->invitation->role}. Email={$this->invitation->email}. Passe: {$this->plainPassword}. Lien: " . url("/invitation/accept/{$this->invitation->token}")
        ];
    }
}
