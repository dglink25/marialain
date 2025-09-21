<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Mail\StudentValidated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\StudentPayment;

class StudentValidationController extends Controller
{
    // Liste des élèves non validés
    public function index()
    {
        $students = Student::where('is_validated', false)->get();
        return view('admin.students.pending', compact('students'));
    }

    // Validation et envoi du mail avec reçu
    public function validateStudent(Request $request, Student $student){
        $request->validate([
            'amount_paid' => 'required|numeric|min:0',
        ]);

        // Mise à jour de l'élève
        $student->update([
            'is_validated' => true,
            'amount_paid' => $request->amount_paid,
        ]);

        $payment = $student->payments()->create([
            'tranche'      => 1,
            'amount'       => $request->amount_paid,
            'payment_date' => now(),
        ]);

        Mail::to($student->parent_email)->send(new StudentValidated($student));

        $student->school_fees_paid = $student->payments()->sum('amount');
        $student->fully_paid = $student->school_fees_paid >= $student->classe->school_fees;
        $student->save();

        return redirect()->route('admin.students.pending')->with('success', 'Élève validé et reçu envoyé avec succès.');
    }
}
