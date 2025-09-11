<?php
namespace App\Mail;


use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;


class InvitationMail extends Mailable
{
    use Queueable, SerializesModels;


    public $invitation;


    public function __construct(Invitation $invitation)
    {
        $this->invitation = $invitation;
    }


    public function build()
    {
        return $this->subject('Invitation à rejoindre la plateforme MARI ALAIN')
        ->view('emails.invitation');
    }
}