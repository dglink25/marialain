<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ParentDashboardController extends Controller
{
    public function index()
    {
        return view('parent.dashboard');
    }

    public function meseleves($student)
    {
        //
    }
}
