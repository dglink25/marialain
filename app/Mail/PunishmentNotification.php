<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;


class PunishmentNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $student;
    public $reason;
    public $hours;

    public function __construct($student, $reason, $hours)
    {
        $this->student = $student;
        $this->reason = $reason;
        $this->hours = $hours;
    }

    public function build()
    {
        return $this->subject("Notification de punition pour {$this->student->full_name}")
                    ->markdown('emails.punishment');
    }
}
