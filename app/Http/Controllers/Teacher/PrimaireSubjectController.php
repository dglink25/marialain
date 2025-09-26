<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Classe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrimaireSubjectController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $classe = Classe::where('teacher_id', $user->id)
            ->whereHas('entity', fn($q) => $q->where('name', 'primaire'))
            ->first();

        if (!$classe) {
            return view('teacher.primaire.subjects', [
                'classe' => null,
                'subjects' => collect(),
                'error' => 'Vous n’êtes assigné à aucune classe primaire.'
            ]);
        }

        $subjects = Subject::where('classe_id', $classe->id)->get();

        return view('teacher.primaire.subjects', compact('classe', 'subjects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'hours' => 'nullable|integer|min:1'
        ]);

        $user = Auth::user();
        $classe = Classe::where('teacher_id', $user->id)
            ->whereHas('entity', fn($q) => $q->where('name', 'primaire'))
            ->first();

        if (!$classe) {
            return redirect()->route('teacher.subjects.primaire')
                ->with('error', 'Impossible d’ajouter la matière car aucune classe primaire n’est assignée.');
        }

        Subject::create([
            'name'      => $request->name,
            'hours'     => $request->hours,
            'classe_id' => $classe->id,
        ]);

        return redirect()->route('teacher.subjects.primaire')
            ->with('success', 'Matière ajoutée avec succès');
    }

    public function update(Request $request, Subject $subject)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'hours' => 'nullable|integer|min:1'
        ]);

        $subject->update([
            'name'  => $request->name,
            'hours' => $request->hours,
        ]);

        return redirect()->route('teacher.subjects.primaire')
            ->with('success', 'Matière modifiée avec succès');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();

        return redirect()->route('teacher.subjects.primaire')
            ->with('success', 'Matière supprimée');
    }
}
