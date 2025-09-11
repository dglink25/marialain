<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AcademicYear;

class AcademicYearController extends Controller
{
    public function index()
    {
        $years = AcademicYear::all();
        return view('admin.academic_years.index', compact('years'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:academic_years,name',
            'active' => 'required|boolean'
        ]);
        if ($request->active == 1 && AcademicYear::where('active', 1)->exists()) {
            return back()->withErrors(['active' => 'Une seule année scolaire peut être active à la fois.'])->withInput();
        }
        AcademicYear::create([
            'name' => $request->name,
            'active' => $request->active,
        ]);
        return redirect()->route('admin.academic_years.index')->with('success', 'Année académique créée.');
    }

    public function edit(AcademicYear $academicYear)
    {
        return view('admin.academic_years.edit', compact('academicYear'));
    }

    public function update(Request $request, AcademicYear $academicYear)
    {
        $request->validate([
            'name' => 'required|string|unique:academic_years,name,' . $academicYear->id,
            'active' => 'required|boolean'
        ]);

        if ($request->active == 1 && AcademicYear::where('active', 1)->exists()) {
            return back()->withErrors(['active' => 'Une seule année scolaire peut être active à la fois.'])->withInput();
        }

        $academicYear->update([
            'name' => $request->name,
            'active' => $request->active,
        ]);

        return redirect()->route('admin.academic_years.index')
                         ->with('success', 'Année académique mise à jour.');
    }

    public function destroy(AcademicYear $academicYear)
    {
        
        $academicYear->delete();

        return redirect()->route('admin.academic_years.index')
                         ->with('success', 'Année académique supprimée. Toutes les données associées ont été perdues.');
    }


}
