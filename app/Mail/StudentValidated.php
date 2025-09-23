<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class StudentValidated extends Mailable{
    use Queueable, SerializesModels;

    public $student;

    public function __construct(Student $student) {
        $this->student = $student;
    }

    public function build(){
        // Génération du PDF
        $pdf = Pdf::loadView('pdf.valide_pdf', ['student' => $this->student]);

        // Enregistrement dans storage
        $fileName = 'receipts/recu_' . $this->student->id . '_' . time() . '.pdf';
        Storage::disk('public')->put($fileName, $pdf->output());

        // Envoi avec pièce jointe
        return $this->subject('Inscription validée - Reçu')
            ->markdown('emails.students.validated', [
                'student' => $this->student,
            ])
            ->attach(Storage::disk('public')->path($fileName));
    }

}
