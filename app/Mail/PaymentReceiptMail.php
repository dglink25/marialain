<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\StudentPayment;

class PaymentReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public $payment;
    public $pdfPath;

    public function __construct(StudentPayment $payment, $pdfPath)
    {
        $this->payment = $payment;
        $this->pdfPath = $pdfPath;
    }

    public function build()
    {
        return $this->subject('Reçu de paiement scolaire')
                    ->markdown('emails.receipt')
                    ->attach($this->pdfPath, [
                        'as' => 'recu_paiement.pdf',
                        'mime' => 'application/pdf',
                    ]);
    }
}
