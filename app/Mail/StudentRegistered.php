<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Student;

class StudentRegistered extends Mailable{
    use Queueable, SerializesModels;

    public $student;

    public function __construct(Student $student) {
        $this->student = $student;
    }

    public function build() {
        return $this->subject("Inscription de {$this->student->first_name} {$this->student->last_name}")
                    ->markdown('emails.students.registered');
    }
}
