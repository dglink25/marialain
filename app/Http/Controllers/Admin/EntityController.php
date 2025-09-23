<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Entity;

class EntityController extends Controller
{
    public function getClasses(Entity $entity)
    {
        return response()->json($entity->classes()->select('id','name')->get());
    }
}
