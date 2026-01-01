<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Classe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\StudentRegistered;
use App\Mail\StudentValidated;

class StudentsController extends Controller
{
    public function create()
    {
        $entities = \App\Models\Entity::all();
        return view('admin.students.inscription', compact('entities'));
    }

public function store(Request $request){
        $request->validate([
            'last_name' => 'required|string',
            'first_name' => 'required|string',
            'birth_date' => 'required|date',
            'birth_place' => 'required|string',
            'gender' => 'required|in:M,F',
            'num_educ' => 'required|string|unique:students,num_educ',
            'entity_id' => 'required|exists:entities,id',
            'classe_id' => 'required|exists:classes,id',
            'birth_certificate' => 'required|mimes:pdf',
            'vaccination_card' => 'nullable|mimes:pdf',
            'previous_report_card' => 'nullable|mimes:pdf',
            'diploma_certificate' => 'nullable|mimes:pdf',
            'parent_full_name' => 'required|string',
            'parent_email' => 'required|email',
            'parent_phone' => 'required|string',
        ]);

        // Sauvegarde fichiers
        $data = $request->all();
        foreach (['birth_certificate','vaccination_card','previous_report_card','diploma_certificate'] as $file) {
            if ($request->hasFile($file)) {
                $data[$file] = $request->file($file)->store("students/{$file}", 'public');
            }
        }

        $student = Student::create($data);

        // Envoi email confirmation au parent
        Mail::to($student->parent_email)->send(new StudentRegistered($student));

        return back()->with('success', 'Inscription réussie. Un email a été envoyé au parent.');
    }

    public function validateRegistration(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $request->validate([
            'registration_fee' => 'required|numeric|min:0',
        ]);

        $student->update([
            'validated' => true,
            'registration_fee' => $request->registration_fee,
        ]);

        // Envoi email confirmation après validation
        Mail::to($student->parent_email)->send(new StudentValidated($student));

        return back()->with('success', "Inscription validée et email envoyé.");
    }
}
    