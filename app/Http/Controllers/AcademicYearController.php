<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AcademicYear;

class AcademicYearController extends Controller{
    public function index()
    {
        $years = AcademicYear::all();
        return view('academic_years.index', compact('years'));
    }

    public function switch(Request $request)
    {
        $request->validate(['year_id' => 'required|exists:academic_years,id']);
        session(['academic_year_id' => $request->year_id]);

        return back()->with('success', 'Année académique changée avec succès !');
    }
}
