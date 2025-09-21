<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Classe; // ✅ modèle correct
use App\Models\AcademicYear;
use App\Models\Entity;

class ClassController extends Controller{
    public function checkActiveYear(){
        $activeYear = AcademicYear::where('active', 1)->first();
        if (!$activeYear) {
            // Retourner une vue d’erreur si pas d’année active
            return view('errors.no_active_year');
        }
        return $activeYear;
    }

    public function index(Request $request)
    {
        $entities = Entity::all();
        $years = AcademicYear::where('active', 1)->get();

        $query = Classe::with('entity', 'academicYear');

        if ($request->has('entity_id') && $request->entity_id != '') {
            $query->where('entity_id', $request->entity_id);
        }

        $classes = $query->paginate(10)->withQueryString();

        return view('admin.classes.index', compact('classes', 'entities', 'years'));
    }

    public function edit($id){
        if (!$this->checkActiveYear() instanceof AcademicYear) {
            return $this->checkActiveYear();
        }
        $classe = Classe::findOrFail($id);
        $entities = Entity::all();
        $years = AcademicYear::where('active', 1)->get();

        return view('admin.classes.edit', compact('classe', 'entities', 'years'));
    }

    public function update(Request $request, $id){
        if (!$this->checkActiveYear() instanceof AcademicYear) {
            return $this->checkActiveYear();
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'academic_year_id' => 'required|exists:academic_years,id',
            'entity_id' => 'required|exists:entities,id',
            'school_fees' => 'required|numeric|min:0',
        ]);

        $classe = Classe::findOrFail($id);
        $classe->update($request->only('name', 'academic_year_id', 'entity_id', 'school_fees'));

        return redirect()->route('admin.classes.index')->with('success', 'Classe mise à jour.');
    }

    public function destroy($id){
        if (!$this->checkActiveYear() instanceof AcademicYear) {
            return $this->checkActiveYear();
        }
        $classe = Classe::findOrFail($id);
        $classe->delete();

        return redirect()->route('admin.classes.index')->with('success', 'Classe supprimée.');
    }

    public function store(Request $request){
        if (!$this->checkActiveYear() instanceof AcademicYear) {
            return $this->checkActiveYear();
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'academic_year_id' => 'required|exists:academic_years,id',
            'entity_id' => 'required|exists:entities,id',
            'school_fees' => 'required|numeric|min:0',
        ]);

        Classe::create($request->only('name', 'academic_year_id', 'entity_id', 'school_fees'));

        return redirect()->route('admin.classes.index')->with('success', 'Classe créée.');
    }
}
