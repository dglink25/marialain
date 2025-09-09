<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class SchoolController extends Controller{
    public function index()
    {
        $schools = School::all();
        return view('admin.schools.index', compact('schools'));
    }

    public function create() { return view('admin.schools.create'); }

    public function store(Request $r){
        $r->validate(['name'=>'required|string']);
        $s = School::create(['name'=>$r->name,'slug'=>Str::slug($r->name),'description'=>$r->description]);
        return redirect()->route('schools.index')->with('success','École ajoutée');
    }

    public function destroy(School $school) { $school->delete(); return redirect()->back()->with('success','Supprimée'); }
}