<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Year;
use Illuminate\Http\Request;

class YearController extends Controller
{
    public function index()
    {
        $years = Year::all();
        return view('admin.years.index', compact('years'));
    }

    public function create()
    {
        // Affiche le formulaire de création
        return view('admin.years.create');
    }

    public function show($id)
    {
        $year = Year::findOrFail($id);
        $classes = $year->classes()->get();
        $invitations = $year->invitations()->with('createdBy')->get();
        return view('admin.years.show', compact('year', 'classes', 'invitations'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        Year::create($request->all());

        return redirect()->route('admin.years.index')->with('success', 'Année scolaire ajoutée.');
    }

    public function edit(Year $year)
    {
        return view('admin.years.edit', compact('year'));
    }

    public function update(Request $request, Year $year)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $year->update($request->all());

        return redirect()->route('admin.years.index')->with('success', 'Année scolaire mise à jour.');
    }

    public function destroy(Year $year)
    {
        $year->delete();
        return redirect()->route('admin.years.index')->with('success', 'Année scolaire supprimée.');
    }
}
