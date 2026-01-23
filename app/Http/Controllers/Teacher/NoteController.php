<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Classe;
use App\Models\Subject;
use App\Models\Grade;
use App\Models\AcademicYear;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\NoteEditPermission;

class NoteController extends Controller{

    public function index($classId, $subjectId, $trimestre){
        $activeYear = AcademicYear::where('active', true)->first();

        if (!$activeYear) {
            return back()->with('error', 'Aucune année académique active trouvée.');
        }

        // Vérifier si la saisie est autorisée pour le trimestre
        $permission = \App\Models\NotePermission::where('class_id', $classId)
            ->where('trimestre', (string) $trimestre) // forcer string pour PostgreSQL
            ->first();

        if (!$permission || $permission->is_open != 1) {
            return back()->with('error', "La saisie des notes n'est pas encore autorisée pour ce trimestre.");
        }

        // Charger la classe et les élèves avec leurs notes filtrées par matière et trimestre
        $classe = Classe::with('students')->findOrFail($classId);
        $subject = Subject::findOrFail($subjectId);

        foreach ($classe->students as $student) {
            $student->gradesFiltered = $student->grades()
                ->where('academic_year_id', $activeYear->id)
                ->where('subject_id', $subjectId) // 🔹 filtrer par matière
                ->where('trimestre', $trimestre)
                ->get();
        }

        $hasNotes = $classe->students->flatMap->gradesFiltered->isNotEmpty();

        return view('teacher.notes.index', compact('classe', 'subject', 'activeYear', 'trimestre', 'hasNotes'));
    }
    

    public function read($classId, $subjectId, $type, $num, $trimestre){
        $activeYear = AcademicYear::where('active', true)->firstOrFail();

        // Vérifier que l’enseignant enseigne cette matière dans cette classe
        $check = DB::table('class_teacher_subject')
            ->where('class_id', $classId)
            ->where('teacher_id', Auth::id())
            ->where('subject_id', $subjectId)
            ->exists();

        if (!$check) {
            return back()->with('error', "Vous n'êtes pas autorisé à consulter cette matière.");
        }

        $classe = Classe::with(['students' => function($q) use ($activeYear, $subjectId, $type, $num, $trimestre) {
            $q->where('is_validated', 1)
            ->where('academic_year_id', $activeYear->id)
            ->with(['grades' => function($g) use ($activeYear, $subjectId, $type, $num, $trimestre) {
                $g->where('academic_year_id', $activeYear->id)
                    ->where('subject_id', $subjectId)
                    ->where('type', $type)
                    ->where('sequence', $num)
                    ->where('trimestre', $trimestre);
            }])
            ->orderBy('last_name')
            ->orderBy('first_name');
        }])->findOrFail($classId);

        $subject = Subject::findOrFail($subjectId);

        return view('teacher.notes.read', compact('classe','subject','type','num','trimestre'));
    }

    public function chooseTrimestre($classId, $subjectId){
        $classe = Classe::findOrFail($classId);

        // Récupérer la matière
        $subject = \App\Models\Subject::findOrFail($subjectId);

        return view('teacher.notes.trimestres', compact('classe', 'subject'));
    }

    public function create($classId, $subjectId, $type, $num, $trimestre){
        $activeYear = AcademicYear::where('active', true)->firstOrFail();

        // Vérifier que l'enseignant enseigne cette matière
        $check = DB::table('class_teacher_subject')
            ->where('class_id', $classId)
            ->where('teacher_id', Auth::id())
            ->where('subject_id', $subjectId)
            ->exists();

        if (!$check) {
            return back()->with('error', 'Vous ne pouvez pas ajouter des notes pour cette matière.');
        }

        // Vérifier interro précédente
        if ($type === 'interrogation' && $num > 1) {
            $previous = Grade::where('class_id', $classId)
                ->where('subject_id', $subjectId)
                ->where('type', 'interrogation')
                ->where('sequence', $num - 1)
                ->where('academic_year_id', $activeYear->id)
                ->exists();

            if (!$previous) {
                return back()->with('error', "Interrogation " . ($num - 1) . " doit être saisie avant.");
            }
        }

        // Vérifier si déjà existant
        $existing = Grade::where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->where('type', $type)
            ->where('trimestre', $trimestre)
            ->where('sequence', $num)
            ->where('academic_year_id', $activeYear->id)
            ->exists();

        if ($existing) {
            return back()->with('error', "Les notes pour $type $num sont déjà saisies.");
        }

        $classe = Classe::with(['students' => function ($q) use ($activeYear) {
            $q->where('academic_year_id', $activeYear->id)
            ->where('is_validated', 1)
            ->orderBy('last_name')
            ->orderBy('first_name');
        }])->findOrFail($classId);

        $subject = Subject::findOrFail($subjectId);

        return view('teacher.notes.create', compact('classe','subject','type','num','trimestre'));
    }

    public function store(Request $request, $classId, $subjectId, $type, $num, $trimestre){
        $request->validate([
            'notes.*' => 'nullable|numeric|min:0|max:20'
        ]);

        $activeYear = AcademicYear::where('active', true)->firstOrFail();

        // Vérifier enseignement matière
        $check = DB::table('class_teacher_subject')
            ->where('class_id', $classId)
            ->where('teacher_id', Auth::id())
            ->where('subject_id', $subjectId)
            ->exists();

        if (!$check) {
            return back()->with('error', 'Vous ne pouvez pas enregistrer des notes pour cette matière.');
        }

        $classe = Classe::with('students')->findOrFail($classId);

        foreach ($classe->students as $student) {

            if (isset($request->notes[$student->id])) {
                Grade::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'subject_id' => $subjectId,
                        'type' => $type,
                        'sequence' => $num,
                        'trimestre' => $trimestre,
                        'class_id' => $classId,
                        'academic_year_id' => $activeYear->id,
                    ],
                    [
                        'value' => $request->notes[$student->id],
                    ]
                );
            }
        }

        // Charger la classe et les élèves avec leurs notes filtrées par matière et trimestre
        $classe = Classe::with('students')->findOrFail($classId);
        $subject = Subject::findOrFail($subjectId);

        foreach ($classe->students as $student) {
            $student->gradesFiltered = $student->grades()
                ->where('academic_year_id', $activeYear->id)
                ->where('subject_id', $subjectId) // 🔹 filtrer par matière
                ->where('trimestre', $trimestre)
                ->get();
        }

        $hasNotes = $classe->students->flatMap->gradesFiltered->isNotEmpty();

        return redirect()
            ->route('teacher.notes.index', [$classId, $subjectId, $type, $num, $trimestre, $classe, $subject, $activeYear, $hasNotes])
                                        ->with('success', 'Notes enregistrées avec succès.');

    }

    public function edit($classId, $subjectId, $type, $num, $trimestre){
        $activeYear = AcademicYear::where('active', true)->firstOrFail();
       
        // Vérifier l’autorisation avant tout
        $teacherId = Auth::id(); // enseignant connecté
        
        $permission = NoteEditPermission::where('teacher_id', $teacherId)
            ->where('class_id', $classId)
            ->where('academic_year_id', $activeYear->id)
            ->where('trimestre', $trimestre)
            ->where('type', $type)
            ->where('subject_id', $subjectId)
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->first();

        if (!$permission) {
            return back()->with('error', "⚠️ Vous n'êtes pas autorisé à modifier les notes de $type $num pour ce trimestre. 
            Veuillez contacter le censeur pour obtenir l'autorisation.");
        }

        $subject = Subject::where('id', $subjectId)->firstOrFail();
      
        // Si autorisation trouvée, afficher la vue d’édition
        $classe = Classe::with(['students' => function($q) use ($type, $num, $activeYear, $trimestre, $subjectId) {
            $q->where('academic_year_id', $activeYear->id)
                ->where('is_validated', 1)
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->with(['grades' => function($q2) use ($type, $num, $activeYear, $trimestre, $subjectId) {
                    $q2->where('type', $type)
                        ->where('sequence', $num)
                        ->where('subject_id', $subjectId)
                        ->where('trimestre', $trimestre)
                        ->where('academic_year_id', $activeYear->id);
                }]);
        }])->findOrFail($classId);

        return view('teacher.notes.edit', compact('classe','type','num','trimestre', 'subject'));
    }

    // Mettre à jour les notes
    public function update(Request $request, $classId, $subjectId, $num, $type, $trimestre){
        $request->validate([
            'notes.*' => 'nullable|numeric|min:0|max:20'
        ]);

        $activeYear = AcademicYear::where('active', true)->firstOrFail();

        // Vérifier autorisation
        $permission = NoteEditPermission::where('teacher_id', Auth::id())
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->where('academic_year_id', $activeYear->id)
            ->where('trimestre', $trimestre)
            ->where('type', $type)
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->first();

        if (!$permission) {
            return back()->with('error','Modification non autorisée.');
        }

        foreach ($request->notes as $studentId => $val) {
            $criteria = [
                'student_id' => $studentId,
                'subject_id' => $subjectId,
                'type' => $type,
                'sequence' => $num,
                'trimestre' => $trimestre,
                'class_id' => $classId,
                'academic_year_id' => $activeYear->id
            ];

            // Vérifier si une note existe déjà
            $existingGrade = Grade::where($criteria)->first();

            if (is_null($val) || $val === '') {
                // Supprimer l'enregistrement si la note est null/vide
                if ($existingGrade) {
                    $existingGrade->delete();
                }
            } else {
                // Mettre à jour ou créer la note
                Grade::updateOrCreate(
                    $criteria,
                    ['value' => $val]
                );
            }
        }

        return redirect()
            ->route('teacher.classes.notes.read', [$classId, $subjectId, $type, $num, $trimestre])
            ->with('success','Notes mises à jour.');
    }

    // Supprimer toutes les notes pour ce type/séquence
    public function destroy($classId, $type, $num, $trimestre){
        $activeYear = AcademicYear::where('active', true)->firstOrFail();
        $subject = DB::table('class_teacher_subject')
            ->where('class_id', $classId)
            ->where('teacher_id', Auth::id())
            ->first();

        if (!$subject) {
            return back()->with('error','Aucune matière assignée à cet enseignant.');
        }

        Grade::where('class_id', $classId)
            ->where('subject_id', $subject->subject_id)
            ->where('type', $type)
            ->where('sequence', $num)
            ->where('academic_year_id', $activeYear->id)
            ->delete();

        return redirect()->route('teacher.classes.notes.read', [$classId, $type, $num, $trimestre])
                         ->with('success','Toutes les notes ont été supprimées.');
    }

    public function showClassNotesAll($classId, $trimestre){
        $activeYear = AcademicYear::where('active', true)->first();
        if (!$activeYear) {
            return back()->with('error', 'Aucune année académique active trouvée.');
        }

        $classe = Classe::with(['students' => function ($q) {
            $q->orderBy('last_name')->orderBy('first_name');
        }, 'subjects'])->findOrFail($classId);

        // Préparer les notes de chaque élève par matière et type
        $gradesData = [];
        foreach ($classe->students as $student) {
            $studentGrades = [];
            foreach ($classe->subjects as $subject) {
                $grades = Grade::where('student_id', $student->id)
                    ->where('subject_id', $subject->id)
                    ->where('academic_year_id', $activeYear->id)
                    ->where('trimestre', $trimestre)
                    ->get();

                $interros = $grades->where('type', 'interrogation')->sortBy('sequence')->pluck('value')->toArray();
                $devoirs  = $grades->where('type', 'devoir')->sortBy('sequence')->pluck('value')->toArray();

                $moyenneInterro = count($interros) > 0 ? round(array_sum($interros)/count($interros), 2) : null;
                $coef = $subject->coefficient ?? 1;
                $moyenneMat = $moyenneInterro && count($devoirs) > 0
                    ? round((($moyenneInterro + array_sum($devoirs)/count($devoirs)) / 2) * $coef, 2)
                    : null;

                if ($moyenneInterro !== null) {
                    $totalNotes = $moyenneInterro + array_sum($devoirs);
                    $nombreNotes = 1 + count($devoirs);

                    $moyenneMat = round(($totalNotes / $nombreNotes), 2);
                } 
                else {
                    $moyenneMat = null;
                }


                $studentGrades[$subject->id] = [
                    'interros' => $interros,
                    'devoirs' => $devoirs,
                    'moyenneInterro' => $moyenneInterro,
                    'coef' => $coef,
                    'moyenneMat' => $moyenneMat,
                ];

            }   
            
            $gradesData[$student->id] = $studentGrades;
            dd($gradesData);
        }

        return view('teacher.notes.class_notes', compact('classe', 'gradesData', 'activeYear', 'trimestre'));
    }

    public function showClassNotes($classId, $subjectId, $trimestre){
        // 1) année académique active
        $activeYear = AcademicYear::where('active', true)->first();
        if (!$activeYear) {
            return back()->with('error', 'Aucune année académique active trouvée.');
        }

        // 2) récupérer la classe avec les élèves validés
        $classe = Classe::with(['students' => function ($q) use ($activeYear) {
            $q->where('is_validated', 1)
            ->where('academic_year_id', $activeYear->id)
            ->orderBy('last_name')
            ->orderBy('first_name');
        }])->findOrFail($classId);

        // 3) Vérifier que l’enseignant enseigne BIEN cette matière dans cette classe
        $teacherId = Auth::id();

        $pivotTable = 'class_teacher_subject';

        $exists = DB::table($pivotTable)
            ->where('class_id', $classId)
            ->where('teacher_id', $teacherId)
            ->where('subject_id', $subjectId)
            ->exists();

        if (!$exists) {
            return back()->with('error', "Vous n'êtes pas autorisé à consulter les notes de cette matière.");
        }

        // Récupérer la matière concernée
        $subject = Subject::findOrFail($subjectId);

        // 4) Préparation des notes pour CHAQUE élève
        $gradesData = [];
        $classeMoyennes = [];

        foreach ($classe->students as $student) {

            // Récupérer les notes de cet élève pour cette matière
            $grades = Grade::where('student_id', $student->id)
                ->where('subject_id', $subjectId)
                ->where('academic_year_id', $activeYear->id)
                ->where('trimestre', $trimestre)
                ->get();

            // Séparer interros et devoirs
            $interros = [];
            $devoirs = [];

            foreach ($grades as $grade) {
                if ($grade->type === 'interrogation') {
                    $interros[$grade->sequence] = $grade->value;
                }
                if ($grade->type === 'devoir') {
                    $devoirs[$grade->sequence] = $grade->value;
                }
            }

            ksort($interros);
            ksort($devoirs);

            // Calcul moyennes
            $moyenneInterro = count($interros)
                ? round(array_sum($interros) / count($interros), 2)
                : null;
            $moyenneDevoir = count($devoirs) ? round(array_sum($devoirs) / count($devoirs), 2) : null;
            
            $coef = $subject->coefficient ?? 1;

            $moyenne = null;
            $moyenneMat = null;

            // Nouvelle logique : interro moyenne + devoirs directs
            if ($moyenneInterro !== null) {

                $total = $moyenneInterro + array_sum($devoirs);
                $nbNotes = 1 + count($devoirs);

                $moyenne = round($total / $nbNotes, 2);
                $moyenneMat = round($moyenne * $coef, 2);

                // Pour le classement (NON coefficienté)
                $classeMoyennes[$student->id] = $moyenne;
            }
            // Stocker
            $gradesData[$student->id] = [
                'interros' => $interros,
                'devoirs' => $devoirs,
                'moyenneInterro' => $moyenneInterro,
                'moyenneDevoir' => $moyenneDevoir,
                'moyenne' => $moyenne,
                'moyenneMat' => $moyenneMat,
                'coef' => $coef,
                'subject' => $subject,
                'rang' => null,
            ];
        }

        // 5) Classement
        if (!empty($classeMoyennes)) {
            arsort($classeMoyennes);

            $rank = 1;
            $prev = null;
            $sameCount = 0;

            foreach ($classeMoyennes as $studentId => $moy) {

                if ($moy === $prev) {
                    $sameCount++;
                } else {
                    $rank += $sameCount;
                    $sameCount = 1;
                }

                $gradesData[$studentId]['rang'] = $rank;
                $prev = $moy;
            }
        }

        // 6) retour à la vue
        return view('teacher.notes.class_notes', [
            'classe' => $classe,
            'subject' => $subject,
            'gradesData' => $gradesData,
            'activeYear' => $activeYear,
            'trimestre' => $trimestre,
        ]);
    }

    public function calcInterro($classId){
        // ⚡ calculer moyenne interro par élève (minimum 2 notes)
    }

    public function calcTrimestre($classId) {
        // ⚡ calculer moyenne trimestrielle et sauvegarder
    }
}

