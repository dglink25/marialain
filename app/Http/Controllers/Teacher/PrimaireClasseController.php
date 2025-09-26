<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use Illuminate\Support\Facades\Auth;

class PrimaireClasseController extends Controller
{
    public function index()
    {
        try {
            $user = Auth::user();

            // Vérifie si l’enseignant a une classe assignée
            $classe = Classe::with(['students', 'entity'])
                ->where('teacher_id', $user->id)
                ->whereHas('entity', fn($q) => $q->where('name', 'primaire'))
                ->first();

            if (!$classe) {
                return back()->with('error', 'Aucune classe primaire assignée à votre compte.');
            }

            return view('teacher.primaire.classes', compact('classe'));
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }
}
