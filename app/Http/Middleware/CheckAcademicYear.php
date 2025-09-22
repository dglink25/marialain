<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AcademicYear;

class CheckAcademicYear
{
    public function handle(Request $request, Closure $next)
    {
        $activeYear = AcademicYear::where('active', true)->first();

        if (!$activeYear) {
            return redirect()->route('academic_year.select')
                ->with('error', 'Veuillez sélectionner une année académique active avant de continuer.');
        }

        // Stocker en session pour le réutiliser partout
        session(['academic_year_id' => $activeYear->id]);

        return $next($request);
    }
}
