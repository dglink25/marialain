<?php 

namespace App\Http\Controllers\Admin;

use App\Models\AcademicYear;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AcademicYearController extends Controller{
    public function index(){
        $years = AcademicYear::all();
        return view('admin.academic_years.index', compact('years'));
    }

    public function create()
    {
        return view('admin.academic_years.create');
    }

    public function store(Request $request){
        $request->validate([
            'name' => 'required|unique:academic_years,name',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        if ($request->has('is_active')) {
            AcademicYear::query()->update(['is_active' => false]); // désactiver les autres
        }

        AcademicYear::create($request->all());

        return redirect()->route('academic_years.index')
            ->with('success', 'Année académique créée avec succès');
    }
}
