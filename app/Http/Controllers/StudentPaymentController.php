<?php 

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\PaymentReceiptMail;
use App\Models\AcademicYear;

class StudentPaymentController extends Controller
{
    // Afficher les paiements d'un étudiant
    public function index(Student $student) {
        $student->load('classe'); // Charger la classe pour avoir les frais
        $payments = $student->payments()->latest()->get();
        
        // Calculer les totaux avec le nouveau système
        $totalFees = $student->total_fees ?? $student->classe->school_fees ?? 0;
        $totalPaid = $student->payments()->sum('amount');
        $remainingFees = $totalFees - $totalPaid;
        
        return view('admin.students.payments.index', compact('student', 'payments', 'totalFees', 'totalPaid', 'remainingFees'));
    }

    // Formulaire pour ajouter un paiement
    public function create(Student $student){
        $student->load('classe');
        
        // Utiliser total_fees au lieu de school_fees
        $totalFees = $student->total_fees ?? $student->classe->school_fees ?? 0;
        $totalPaid = $student->payments()->sum('amount');
        $remainingFees = $totalFees - $totalPaid;
        
        return view('admin.students.payments.create', compact('student', 'totalFees', 'remainingFees'));
    }

    // Stocker un paiement
    public function store(Request $request, Student $student){
        $activeAcademicYear = AcademicYear::where('active', true)->first();
        $student->load('classe');
        
        // Calculer le montant total des frais
        $totalFees = $student->total_fees ?? $student->classe->school_fees ?? 0;
        $totalPaid = $student->payments()->sum('amount');
        $remainingFees = $totalFees - $totalPaid;
        
        $request->validate([
            'tranche' => 'required|integer|min:1|max:3',
            'amount' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) use ($remainingFees) {
                    if ($value > $remainingFees) {
                        $fail("Le montant ne peut pas dépasser le reste à payer (" . number_format($remainingFees, 0, ',', ' ') . " FCFA)");
                    }
                },
            ],
            'payment_date' => 'required|date|before_or_equal:today',
        ]);

        // Créer le paiement
        $payment = $student->payments()->create([
            'tranche' => $request->tranche,
            'amount' => $request->amount,
            'academic_year_id' => $activeAcademicYear->id,
            'payment_date' => $request->payment_date,
        ]);

        // Mettre à jour amount_paid dans la table students
        $newTotalPaid = $totalPaid + $request->amount;
        $student->update([
            'amount_paid' => $newTotalPaid,
            'is_validated' => $newTotalPaid >= $totalFees ? 1 : $student->is_validated, // Valider si tout payé
        ]);

        // Générer PDF du reçu
        try {
            $pdf = Pdf::loadView('pdf.receipt', [
                'student' => $student,
                'payment' => $payment,
                'totalFees' => $totalFees,
                'totalPaid' => $newTotalPaid,
                'remainingFees' => $totalFees - $newTotalPaid
            ]);
            
            $pdfPath = 'receipts/recu_' . $payment->id . '_' . time() . '.pdf';
            Storage::disk('public')->put($pdfPath, $pdf->output());

            // Mettre à jour la colonne receipt
            $payment->update(['receipt' => $pdfPath]);

            // Envoyer l'email si l'élève a un email parent
            if ($student->parent_email) {
                try {
                   // Mail::to($student->parent_email)->send(new PaymentReceiptMail($payment, Storage::disk('public')->path($pdfPath)));
                } catch (\Exception $e) {
                    // Log l'erreur mais ne pas bloquer le processus
                   // Log::error('Erreur envoi email: ' . $e->getMessage());
                }
            }
        } catch (\Exception $e) {
            //Log::error('Erreur génération PDF: ' . $e->getMessage());
            // Continuer même sans PDF
        }

        return redirect()->route('students.payments.index', $student->id)
                        ->with('success', 'Paiement de ' . number_format($request->amount, 0, ',', ' ') . ' FCFA enregistré avec succès.');
    }

    // Mettre à jour le type d'inscription d'un étudiant
    public function updateRegistrationType(Request $request, Student $student) {
        $request->validate([
            'registration_type' => 'required|in:new,re_registration',
        ]);

        $student->load('classe');
        
        // Recalculer les frais totaux en fonction du nouveau type d'inscription
        $totalFees = $student->classe->school_fees ?? 0;
        
        if ($request->registration_type === 'new') {
            $totalFees += $student->classe->registration_fee ?? 0;
        } elseif ($request->registration_type === 're_registration') {
            $totalFees += $student->classe->re_registration_fee ?? 0;
        }

        // Mettre à jour l'étudiant
        $student->update([
            'registration_type' => $request->registration_type,
            'total_fees' => $totalFees,
        ]);

        return redirect()->back()->with('success', 
            'Type d\'inscription mis à jour. Nouveaux frais totaux: ' . number_format($totalFees, 0, ',', ' ') . ' FCFA');
    }

    // Télécharger un reçu
    public function downloadReceipt(StudentPayment $payment){
        if ($payment->receipt && Storage::disk('public')->exists($payment->receipt)) {
            return Storage::disk('public')->download($payment->receipt);
        }
        
        // Si pas de fichier, générer à la volée
        $student = $payment->student;
        $totalFees = $student->total_fees ?? $student->classe->school_fees ?? 0;
        $totalPaid = $student->payments()->sum('amount');
        
        $pdf = Pdf::loadView('pdf.receipt', [
            'student' => $student,
            'payment' => $payment,
            'totalFees' => $totalFees,
            'totalPaid' => $totalPaid,
            'remainingFees' => $totalFees - $totalPaid
        ]);
        
        return $pdf->download('recu_' . $payment->id . '.pdf');
    }
}