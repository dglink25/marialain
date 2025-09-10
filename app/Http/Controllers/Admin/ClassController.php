<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\School;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class ClassController extends Controller{
    public function index(){
        $classes = SchoolClass::with('school','academicYear')->paginate(20);
        return view('admin.classes.index', compact('classes'));
    }

    public function create()
    {
        $schools = School::all();
        $years = AcademicYear::all();
        return view('admin.classes.create', compact('schools','years'));
    }

    public function store(Request $r)
    {
        $r->validate(['school_id'=>'required|exists:schools,id','name'=>'required|string']);
        SchoolClass::create($r->only(['school_id','name','level','series','academic_year_id']));
        return redirect()->route('classes.index')->with('success','Classe créée');
    }

    public function destroy(SchoolClass $class) { $class->delete(); return redirect()->back()->with('success','Supprimée'); }
}