<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Mail\StudentValidated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\StudentPayment;
use App\Models\AcademicYear;
use Cloudinary\Cloudinary;
use Cloudinary\Api\Upload\UploadApi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;


class StudentValidationController extends Controller{

    public function checkActiveYear(){
        $activeYear = AcademicYear::where('active', 1)->first();
        if (!$activeYear) {
            // Retourner une vue d’erreur si pas d’année active
            return view('errors.no_active_year');
        }
        return $activeYear;
    }

    // Liste des élèves non validés
    public function index(){
        try {
            // Vérifier si une année scolaire est active
            $activeYear = AcademicYear::where('active', true)->first();

            if (!$activeYear) {
                return view('admin.students.pending', [
                    'students' => collect(),  // liste vide
                    'activeYear' => null,
                    'message' => 'Aucune année scolaire active pour le moment.'
                ]);
            }


            // Récupérer les élèves non validés de l'année active
            $students = Student::where('is_validated', false)
                            ->where('academic_year_id', $activeYear->id)
                            ->get();

            return view('admin.studentspending', compact('student', 'activeYear'));

        } catch (\Exception $e) {
            // Gestion d'autres erreurs inattendues
            return redirect()->back()->with('error', 'Une erreur est survenue : '.$e->getMessage());
        }
    }


    // Validation et envoi du mail avec reçu
    public function validateStudent(Request $request, Student $student){
        if (!$this->checkActiveYear() instanceof AcademicYear) {
            return $this->checkActiveYear();
        }

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

        // Génération du PDF
        $pdf = Pdf::loadView('pdf.valide_pdf', ['student' => $this->student]);

        // Stockage temporaire local
        $tempPath = storage_path('app/temp/' . 'recu_' . $this->student->id . '_' . time() . '.pdf');
        file_put_contents($tempPath, $pdf->output());

        // Upload sur Cloudinary
        $uploadApi = new UploadApi();
        $uploaded = $uploadApi->upload($tempPath, [
            'folder' => 'receipts',  // dossier Cloudinary
            'resource_type' => 'raw' // très important pour PDF
        ]);

        // URL sécurisée du PDF sur Cloudinary
        $pdfUrl = $uploaded['secure_url'];

        // Supprime le fichier temporaire si tu veux
        @unlink($tempPath);

        // Enregistre l'URL dans la base ou retourne-la
        $this->student->receipt_url = $pdfUrl;
        $this->student->save();

        //Mail::to($student->parent_email)->send(new StudentValidated($student));

        $student->school_fees_paid = $student->payments()->sum('amount');
        $student->fully_paid = $student->school_fees_paid >= $student->classe->school_fees;
        $student->save();

        return redirect()->route('admin.students.pending')->with('success', 'Élève validé et reçu envoyé avec succès.');
    }
}
