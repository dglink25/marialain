<?php

namespace App\Http\Controllers\Dprimaire;

use App\Http\Controllers\Controller;
use App\Models\TeacherInvitation;
use App\Models\User;
use App\Models\Classe;
use Illuminate\Http\Request;

class primaryteacherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
       $teachers = User::whereHas('role', function ($query) {$query->where('name', 'teacher');})->get();
       $class = Classe::whereHas('entity', function($query){ $query->where('name', 'primaire'); })->with('academicYear')->get();
            return view('primaire.enseignants.enseignants', compact('teachers', 'class'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
