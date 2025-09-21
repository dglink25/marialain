<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    
    public function activate(Request $request){
        AcademicYear::query()->update(['active' => false]); // désactiver toutes
        $year = AcademicYear::findOrFail($request->year_id);
        $year->update(['active' => true]);

        return redirect()->route('censeur.dashboard')
            ->with('success', "Année académique {$year->name} activée avec succès !");
    }
    public function select(Request $request){
        $year = AcademicYear::findOrFail($request->year_id);

        if (!$year->active) {
            return redirect()->back()->with('error', 'Cette année académique n\'est pas active.');
        }

        // Stocker en session
        session(['academic_year_id' => $year->id]);

        return redirect()->back()->with('success', 'Année académique sélectionnée : ' . $year->name);
    }

}
