<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Entity;

class EntityController extends Controller
{
    /**
     * Retourne les classes d'une entité pour l'année académique active uniquement.
     * Utilisé par le AJAX des formulaires d'inscription (cycle → classes).
     */
    public function getClasses(Entity $entity)
    {
        $activeYear = AcademicYear::where('active', true)->first();

        $query = $entity->classes()->select('id', 'name')->orderBy('name');

        if ($activeYear) {
            $query->where('academic_year_id', $activeYear->id);
        }

        return response()->json($query->get());
    }
}
