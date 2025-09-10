<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Year;
use Illuminate\Http\Request;

class SchoolClassController extends Controller
{
    public function create()
    {
        $years = Year::all(); 
        return view('admin.classes.create', compact('years'));
    }
    

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sector' => 'required|in:Maternelle,Secondaire',
            'level' => 'required|string',
            'series' => 'nullable|string',
            'year_id' => 'required|exists:years,id',
        ]);

        SchoolClass::create($request->all());

        return redirect()->route('admin.years.show', $request->year_id)
                         ->with('success','Classe ajoutée');
    }
}
