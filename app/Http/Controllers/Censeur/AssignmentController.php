<?php 

namespace App\Http\Controllers\Censeur;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Classe;
use App\Models\Subject;
use App\Models\User;
use App\Models\TeacherInvitation;

class AssignmentController extends Controller{
    public function index(Request $request) {
        $classes = Classe::where('entity_id', 3)->get();
        $subjects = Subject::all();

        // Filtrage
        $query = User::whereHas('role', function($q) {
            $q->where('name', 'teacher');
        })
        ->whereHas('invitations', function($q) {
            $q->where('accepted', true);
        });

        if ($request->filled('subject_id')) {
            $query->whereHas('classes', function($q) use ($request) {
                $q->wherePivot('subject_id', $request->subject_id);
            });
        }

        if ($request->filled('class_id')) {
            $query->whereHas('classes', function($q) use ($request) {
                $q->where('classes.id', $request->class_id);
            });
        }

        if ($request->filled('name')) {
            $query->where('name', 'like', '%'.$request->name.'%');
        }

        $teachers = $query->with('classes')->get();

        return view('censeur.assignments.index', compact('classes','subjects','teachers'));
    }

    public function store(Request $request) {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'teacher_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        $class = Classe::findOrFail($request->class_id);

        $exists = $class->teachers()
                        ->wherePivot('teacher_id', $request->teacher_id)
                        ->wherePivot('subject_id', $request->subject_id)
                        ->exists();

        if ($exists) {
            return back()->with('error', 'Cet enseignant enseigne déjà cette matière dans cette classe.');
        }

        $class->teachers()->attach($request->teacher_id, [
            'subject_id' => $request->subject_id
        ]);

        return back()->with('success', 'Attribution réussie.');
    }
}
