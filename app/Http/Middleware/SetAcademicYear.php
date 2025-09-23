<?php

// app/Http/Middleware/SetAcademicYear.php
namespace App\Http\Middleware;

use App\Models\AcademicYear;
use Closure;

class SetAcademicYear
{
    public function handle($request, Closure $next)
    {
        // Récupérer année active
        $year = session('academic_year_id');

        if (!$year) {
            $year = AcademicYear::where('active', true)->first()?->id;
            session(['academic_year_id' => $year]);
        }

        // Partager avec toutes les vues
        view()->share('currentAcademicYear', AcademicYear::find($year));

        return $next($request);
    }
}
