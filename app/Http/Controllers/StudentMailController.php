<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ParentNotificationMail;

class StudentMailController extends Controller{
    
    public function form($id){
        $student = Student::findOrFail($id);

        if (!$student->parent_email) {
            return back()->with('error', "L'élève n'a pas d'email parent enregistré.");
        }

        return view('students.mail_form', compact('student'));
    }

    public function sendToAll(){
        $activeYear = \App\Models\AcademicYear::where('active', true)->first();

        if (!$activeYear) {
            return back()->with('error', 'Aucune année scolaire active trouvée.');
        }

        $students = \App\Models\Student::with('classe')
            ->whereHas('classe', fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->get()
            ->filter(fn($s) => $s->classe && $s->school_fees_paid < $s->classe->school_fees);

        $missingEmails = [];

        foreach ($students as $student) {
            if (!$student->parent_email) {
                $missingEmails[] = $student;
                continue;
            }

            try {
                \Mail::to($student->parent_email)->send(
                    new \App\Mail\ParentNotificationMail(
                        $student,
                        "Notification de scolarité en retard",
                        "Cher parent, votre enfant {$student->full_name} n’a pas encore soldé la scolarité. Merci de régulariser au plus vite."
                    )
                );
            } catch (\Exception $e) {
                \Log::error("Erreur envoi mail à {$student->parent_email}: ".$e->getMessage());
                $missingEmails[] = $student;
            }
        }

        if (count($missingEmails) > 0) {
            return redirect()->back()->with([
                'success' => 'Les mails ont été envoyés avec succès.',
                'warning' => 'Attention : certains élèves n’ont pas d’email de parent enregistré.',
                'missingEmails' => $missingEmails
            ]);
        }

        return redirect()->back()->with('success', 'Les mails de rappel ont été envoyés avec succès !');
    }

}
