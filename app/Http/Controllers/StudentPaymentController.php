<?php 

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\PaymentReceiptMail;


class StudentPaymentController extends Controller{
    // Afficher les paiements d'un étudiant
    public function index(Student $student)
    {
        $payments = $student->payments()->latest()->get();
        return view('admin.students.payments.index', compact('student','payments'));
    }

    // Formulaire pour ajouter un paiement
    public function create(Student $student){
        return view('admin.students.payments.create', compact('student'));
    }

    // Stocker un paiement

    public function store(Request $request, Student $student){
        $request->validate([
            'tranche' => 'required|integer|min:1|max:3',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
        ]);

        $payment = $student->payments()->create($request->only('tranche','amount','payment_date'));

        // Générer PDF
        $pdf = Pdf::loadView('pdf.receipt', compact('payment'));
        $pdfPath = storage_path("app/public/receipts/recu_{$payment->id}.pdf");
        $pdf->save($pdfPath);

        // Mettre à jour la colonne receipt
        $payment->update(['receipt' => "receipts/recu_{$payment->id}.pdf"]);

        // Mettre à jour montant payé et statut
        $student->school_fees_paid = $student->payments()->sum('amount');
        $student->fully_paid = $student->is_fully_paid;
        $student->save();

        // Envoyer l'email si l'élève a un email parent
        if ($student->parent_email) {
            Mail::to($student->parent_email)->send(new PaymentReceiptMail($payment, $pdfPath));
        }

        return redirect()->route('students.payments.index', $student->id)
                        ->with('success','Paiement enregistré et reçu envoyé par email');
    }

}
