<?php

namespace App\Mail;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ParentNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $student;
    public $subjectLine;
    public $messageBody;

    public function __construct(Student $student, $subjectLine, $messageBody)
    {
        $this->student = $student;
        $this->subjectLine = $subjectLine;
        $this->messageBody = $messageBody;
    }

    public function build(){
        return $this->subject($this->subjectLine)
                    ->view('emails.parent_notification')
                    ->with([
                        'student' => $this->student,
                        'messageBody' => $this->messageBody,
                    ]);
    }

}
