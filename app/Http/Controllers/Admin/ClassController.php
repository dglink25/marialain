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
        try {
            // Vérifier si une année scolaire active existe
            $activeYear = AcademicYear::where('active', true)->first();

            if (!$activeYear) {
                return view('admin.classes.index', [
                    'classes' => collect(),   // liste vide
                    'entities' => Entity::all(),
                    'years' => collect(),
                    'activeYear' => null,
                    'message' => 'Aucune année scolaire active pour le moment.'
                ]);
            }


            // Toutes les entités pour le filtre
            $entities = Entity::all();

            // On ne garde que l'année active (inutile de faire un get() ici, sauf si tu veux plusieurs actives)
            $years = AcademicYear::where('active', 1)->get();

            // Récupérer les classes de l'année active
            $query = Classe::with('entity', 'academicYear')
                            ->where('academic_year_id', $activeYear->id);

            // Filtre par entité si sélectionnée
            if ($request->filled('entity_id')) {
                $query->where('entity_id', $request->entity_id);
            }

            $classes = $query->paginate(10)->withQueryString();

            return view('admin.classes.index', compact('classes', 'entities', 'years', 'activeYear'));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', '❌ Erreur inattendue : '.$e->getMessage());
        }
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
