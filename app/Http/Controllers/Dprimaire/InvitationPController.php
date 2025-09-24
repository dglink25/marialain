<?php

namespace App\Http\Controllers\Dprimaire;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\Classe;
use App\Models\TeacherInvitation;
use App\Models\User;
use App\Mail\TeacherInvitationMail;

class InvitationPController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $classes = Classe::whereHas('entity', function($query){ $query->where('name', 'primaire'); })->with('academicYear')->get();
       
        return view('primaire.enseignants.inviter', compact('classes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('primaire.enseignants.inviter');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request -> validate([
            'name'=> 'required|max:255',
            'classe'=> 'required',
            'email' => 'required|email|unique:users,email',
           
        ]);
         $plainPassword = Str::random(8);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($plainPassword),
            'role_id' => 6,
        ]);

        $invitation = TeacherInvitation::create([
            'user_id' => $user->id,
            'name'  => $request->name,   
            'email' => $request->email, 
            'token' => Str::random(32),
            'censeur_id' => Auth::id(),
        ]);

        Mail::to($user->email)->send(new TeacherInvitationMail($invitation, $plainPassword));

        return redirect()->route('primaire.enseignants.enseignants')->with('success', 'Invitation envoyée à '.$user->email);
        
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
