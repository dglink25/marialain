<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\TeacherInvitation;

class TeacherInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $invitation;
    public $plainPassword;

    public function __construct(TeacherInvitation $invitation, $plainPassword)
    {
        $this->invitation = $invitation;
        $this->plainPassword = $plainPassword;
    }

    public function build()
    {
        return $this
            ->subject('Votre compte enseignant - MARI ALAIN')
            ->markdown('emails.teacher_invitation')
            ->with([
                'invitation' => $this->invitation,
                'plainPassword' => $this->plainPassword,
            ]);
    }
}
