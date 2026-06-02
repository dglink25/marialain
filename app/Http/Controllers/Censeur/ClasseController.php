<?php

namespace App\Http\Controllers\Censeur;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\User;
use App\Models\Timetable;
use App\Models\Student;
use App\Models\ParentUser;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class ClasseController extends Controller{
    
    public function index(){
        $activeYear = AcademicYear::where('active', true)->first();

        if (!$activeYear) {
            return view('censeur.classes.index', [
                'classes'    => collect(),
                'activeYear' => null,
                'error'      => "Aucune année scolaire active n'a été trouvée."
            ]);
        }

        $classes = Classe::with(['entity', 'academicYear'])
            ->where('entity_id', 3)
            ->where('academic_year_id', $activeYear->id)
            ->get();

        return view('censeur.classes.index', compact('classes', 'activeYear'));
    }

    public function students($classId){

        $activeYear = AcademicYear::where('active', true)->first();

        if (!$activeYear) {
            return back()->with('error', 'Aucune année scolaire active trouvée.');
        }
        
        $class = Classe::with(['students' => function($query) use ($activeYear) {
            $query->where('is_validated', 1) 
                  ->where('academic_year_id', $activeYear->id)
                  ->orderBy('last_name')
                  ->orderBy('first_name');
        }])->findOrFail($classId);

        return view('censeur.classes.students', compact('class'));
    }

    public function timetable($classId){
        $class = Classe::findOrFail($classId);
        return redirect()->route('censeur.timetables.index', $classId);
    }

    public function teachers($id){
        $class = Classe::with(['timetables.teacher', 'timetables.subject'])->findOrFail($id);

        $teachers = $class->timetables
            ->groupBy('teacher_id')
            ->map(function ($items) {
                $teacher  = $items->first()->teacher;
                $subjects = $items->pluck('subject.name')->unique()->values();
                return [
                    'teacher'  => $teacher,
                    'subjects' => $subjects,
                ];
            });

        return view('censeur.classes.teachers', compact('class', 'teachers'));
    }

    public function downloadStudentsPdf($classId){
        $class = Classe::with(['students' => function($query) {
            $query->where('is_validated', 1);
        }])->findOrFail($classId);

        $students = $class->students->sortBy([
            ['last_name',  'asc'],
            ['first_name', 'asc']
        ]);

        $pdf = Pdf::loadView('censeur.classes.students_pdf', compact('class', 'students'));

        return $pdf->download('eleves_'.$class->name.'.pdf');
    }

    public function export($id){
        $class = Classe::with(['timetables.teacher', 'timetables.subject'])->findOrFail($id);

        $teachers = $class->timetables
            ->groupBy('teacher_id')
            ->map(function ($items) {
                $teacher  = $items->first()->teacher;
                $subjects = $items->pluck('subject.name')->unique()->values();
                return [
                    'teacher'  => $teacher,
                    'subjects' => $subjects,
                ];
            });

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('censeur.teachers.export', [
            'class'    => $class,
            'teachers' => $teachers,
        ])->setPaper('a4', 'landscape');

        return $pdf->download("enseignants_{$class->name}.pdf");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Mise à jour du téléphone du parent
    //
    // PATCH /censeur/classes/{classId}/students/{studentId}/update-phone
    // Nom   : censeur.classes.students.update-phone
    // ─────────────────────────────────────────────────────────────────────────
    public function updateParentPhone(Request $request, int $classId, int $studentId)
    {
        // ── 1. Validation format ────────────────────────────────────────────
        $validated = $request->validate([
            'parent_phone' => [
                'required',
                'string',
                'regex:/^[0-9+\s\-]{8,20}$/',
            ],
        ], [
            'parent_phone.required' => 'Le numéro de téléphone est obligatoire.',
            'parent_phone.regex'    => 'Format invalide. Chiffres, +, espaces ou tirets uniquement (8-20 caractères).',
        ]);

        $newPhone = trim($validated['parent_phone']);

        // ── 2. Récupération sécurisée de l'élève ───────────────────────────
        $student = Student::where('id', $studentId)
                          ->where('class_id', $classId)
                          ->firstOrFail();

        $oldPhone = trim((string) $student->parent_phone);

        // ── 3. Numéro identique → rien à faire ─────────────────────────────
        if ($oldPhone === $newPhone) {
            return response()->json([
                'success'   => true,
                'message'   => 'Le numéro est déjà à jour.',
                'new_phone' => $newPhone,
            ]);
        }

        // ── 4. Le nouveau numéro existe-t-il déjà dans la table parents ? ──
        //
        //   OUI → même parent avec plusieurs enfants (cas normal).
        //          On met à jour UNIQUEMENT students.parent_phone.
        //          La table parents reste intacte → zéro violation UNIQUE.
        //
        //   NON → numéro inédit : on met à jour les deux tables en transaction.
        // ───────────────────────────────────────────────────────────────────
        $phoneExistsInParents = DB::table('parents')
            ->whereRaw("TRIM(CAST(phone AS TEXT)) = ?", [$newPhone])
            ->exists();

        try {

            if ($phoneExistsInParents) {
                // ── Cas : même parent, plusieurs enfants ────────────────────
                // Seule students est mise à jour, pas parents.
                $student->update(['parent_phone' => $newPhone]);

            } else {
                // ── Cas : numéro vraiment nouveau ───────────────────────────
                DB::transaction(function () use ($student, $oldPhone, $newPhone) {
                    $student->update(['parent_phone' => $newPhone]);

                    if ($oldPhone) {
                        ParentUser::whereRaw("TRIM(CAST(phone AS TEXT)) = ?", [$oldPhone])
                                  ->update(['phone' => $newPhone]);
                    }
                });
            }

        } catch (\Illuminate\Database\QueryException $e) {

            // Filet de sécurité : race condition → violation UNIQUE 23505
            // On réessaie en ne touchant QUE students (jamais parents).
            if (($e->errorInfo[0] ?? '') === '23505') {
                try {
                    $student->update(['parent_phone' => $newPhone]);
                } catch (\Throwable $inner) {
                    Log::error('updateParentPhone fallback failed', [
                        'student_id' => $studentId,
                        'error'      => $inner->getMessage(),
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Impossible de mettre à jour le numéro. Veuillez réessayer.',
                    ], 500);
                }
            } else {
                Log::error('updateParentPhone DB error', [
                    'student_id' => $studentId,
                    'class_id'   => $classId,
                    'new_phone'  => $newPhone,
                    'error'      => $e->getMessage(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Une erreur base de données est survenue. Veuillez réessayer.',
                ], 500);
            }

        } 
        catch (\Throwable $e) {
            Log::error('updateParentPhone unexpected error', [
                'student_id' => $studentId,
                'error'      => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Une erreur inattendue est survenue.',
            ], 500);
        }

        $message = $phoneExistsInParents
            ? "Numéro mis à jour. L'élève est rattaché au compte parent existant."
            : 'Numéro de téléphone mis à jour avec succès.';

        return response()->json([
            'success'   => true,
            'message'   => $message,
            'new_phone' => $newPhone,
        ]);
    }
}