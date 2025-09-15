<?php 

namespace App\Http\Controllers\Censeur;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subject;

class SubjectController extends Controller
{
    public function index() {
        $subjects = Subject::all();
        return view('censeur.subjects.index', compact('subjects'));
    }

    public function store(Request $request) {
        $request->validate(['name'=>'required']);
        Subject::create(['name'=>$request->name]);
        return back()->with('success','Matière ajoutée.');
    }
}
