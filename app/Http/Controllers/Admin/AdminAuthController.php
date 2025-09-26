<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function index(){
        $academicYearsCount = \App\Models\AcademicYear::count();
        $classesCount = \App\Models\Classe::count();
        $invitationsCount = \App\Models\Invitation::count();

        return view('admin.dashboard', compact('academicYearsCount','classesCount','invitationsCount'));
    }

    public function accueil(){
        $academicYearsCount = \App\Models\AcademicYear::count();
        $classesCount = \App\Models\Classe::count();
        $invitationsCount = \App\Models\Invitation::count();

        return view('welcome', compact('academicYearsCount','classesCount','invitationsCount'));
    }


    public function createAdmin(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return redirect()->route('admin.dashboard')->with('success', 'Administrateur créé.');
    }
}
