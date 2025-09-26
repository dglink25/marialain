<?php

namespace App\Mail;

use App\Models\TeacherInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TeacherPInvitationMail extends Mailable
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
        return $this->subject('Invitation à rejoindre la plateforme scolaire')
            ->view('emails.teacher_P_invitation');
    }
}
