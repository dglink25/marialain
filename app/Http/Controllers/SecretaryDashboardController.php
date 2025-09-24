<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentPayment;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class SecretaryDashboardController extends Controller
{
    public function index(){
        $activeYear = AcademicYear::where('active', true)->first();

        if (!$activeYear) {
            $user = auth()->user();
            return view('dashboards.secretaire', compact('user'));
        }
        $studentsCount = Student::where('academic_year_id', $activeYear->id)
            ->where('is_validated', 1)
            ->count();

        $pendingRegistrations = Student::where('academic_year_id', $activeYear->id)
            ->where('is_validated', 0)
            ->count();

        $validatedRegistrations = Student::where('academic_year_id', $activeYear->id)
            ->where('is_validated', 1)
            ->count();

        $totalFees = Student::where('academic_year_id', $activeYear->id)
            ->sum('school_fees_paid');
        $recentPayments = StudentPayment::with('student')
            ->where('academic_year_id', $activeYear->id)
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($payment) {
                return [
                    'type' => 'payment',
                    'message' => $payment->student->full_name . ' a payé ' . number_format($payment->amount, 0, ',', ' ') . ' FCFA',
                    'created_at' => $payment->created_at,
                ];
            });

        $recentRegistrations = Student::with('classe')
            ->where('academic_year_id', $activeYear->id)
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($student) {
                return [
                    'type' => 'registration',
                    'message' => $student->full_name . ' a été inscrit en ' . ($student->classe->name ?? '---'),
                    'created_at' => $student->created_at,
                ];
            });

       

        // ✅ Vue avec données
        return view('dashboards.secretaire', [
            'user' => auth()->user(),
            'activeYear' => $activeYear,
            'studentsCount' => $studentsCount,
            'pendingRegistrations' => $pendingRegistrations,
            'validatedRegistrations' => $validatedRegistrations,
            'totalFees' => $totalFees,
            'recentActivities' => 0,
        ]);
    }

    public function unpaidStudents()
    {
        // 🔎 Année académique active
        $activeYear = AcademicYear::where('active', true)->firstOrFail();

        // 🔎 Élèves n'ayant pas fini de solder la scolarité
        $unpaidStudents = Student::with('classe') // charger la classe liée
            ->where('academic_year_id', $activeYear->id)
            ->whereHas('classe', function ($query) {
                $query->whereColumn('students.school_fees_paid', '<', 'classes.school_fees');
            })
            ->get();

        return view('students.unpaid', compact('unpaidStudents', 'activeYear'));
}

}
