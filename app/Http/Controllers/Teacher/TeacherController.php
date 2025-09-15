<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Classe;

class TeacherController extends Controller
{
    public function myClasses() {
        $teacher = Auth::user();
        $classes = $teacher->classes()->with('teachers','timetable')->get();
        return view('teacher.classes', compact('classes'));
    }

    public function classTimetable($classId) {
        $class = Classe::findOrFail($classId);
        $timetables = $class->timetable()->with('subject','teacher')->get();
        return view('teacher.timetable', compact('class','timetables'));
    }
}
