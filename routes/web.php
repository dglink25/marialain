<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvitationMail;
use App\Models\Invitation;

// Controllers
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InvitationResponseController;

// Admin
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\InvitationController as AdminInvitationController;
use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\StudentExportController;

// Censeur
use App\Http\Controllers\Censeur\ClasseController;
use App\Http\Controllers\Censeur\InvitationController as CenseurInvitationController;
use App\Http\Controllers\Censeur\SubjectController;
use App\Http\Controllers\Censeur\AssignmentController;
use App\Http\Controllers\Censeur\TimetableController;

// Teacher
use App\Http\Controllers\Teacher\DashboardController;
use App\Http\Controllers\Teacher\ClassController as TeacherClassController;

//Routes publiques

Route::get('/', fn() => view('accueil'))->name('accueil');
Route::get('/home', fn() => view('welcome'))->name('home');

// Test mail (à supprimer en prod)
Route::get('/test-invitation-mail', function () {
    $inv = Invitation::first();
    if (!$inv) {
        return 'Aucune invitation en base pour test.';
    }
    Mail::to('test@example.com')->send(new InvitationMail($inv));
    return 'Mail d’invitation envoyé !';
});

// Invitation par token
Route::get('/invitation/accept/{token}', [InvitationResponseController::class, 'accept'])
    ->name('invitation.accept');

//Profil (utilisateurs connectés)

Route::middleware('auth')->group(function () {
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profil/updates', [ProfileController::class, 'updates'])->name('profile.update');
    Route::post('/profil/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profil/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
});

// Dashboards par rôle

Route::middleware('auth')->group(function () {
    Route::get('/dashboard/directeur', fn() => view('dashboards.directeur', ['user' => auth()->user()]))
        ->name('directeur.dashboard');

    Route::get('/dashboard/censeur', fn() => view('dashboards.censeur', ['user' => auth()->user()]))
        ->name('censeur.dashboard');

    Route::get('/dashboard/surveillant', fn() => view('dashboards.surveillant', ['user' => auth()->user()]))
        ->name('surveillant.dashboard');

    Route::get('/dashboard/secretaire', fn() => view('dashboards.secretaire', ['user' => auth()->user()]))
        ->name('secretaire.dashboard');
});

// Admin

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminAuthController::class, 'index'])->name('dashboard');
    Route::post('/admins', [AdminAuthController::class, 'createAdmin'])->name('admins.store');

    // Invitations enseignants
    Route::resource('invitations', AdminInvitationController::class)->only(['index', 'store']);

    // Classes
    Route::resource('classes', ClassController::class)->only(['index', 'store', 'create', 'show', 'destroy']);

    // Années académiques
    Route::resource('academic_years', AcademicYearController::class)->only(['index', 'store', 'create']);

    // Étudiants
    Route::resource('students', StudentController::class);
    Route::get('students/list', [StudentController::class, 'listAlphabetical'])->name('students.list');
    Route::get('students/export/pdf', [StudentExportController::class, 'exportPdf'])->name('students.export.pdf');
    Route::get('students/export/excel', [StudentExportController::class, 'exportExcel'])->name('students.export.excel');
});

// Censeur

Route::prefix('censeur')->name('censeur.')->middleware('auth')->group(function () {
    // Invitations enseignants
    Route::get('/invitations', [CenseurInvitationController::class, 'index'])->name('invitations.index');
    Route::post('/invitations', [CenseurInvitationController::class, 'send'])->name('invitations.send');

    // Matières
    Route::resource('subjects', SubjectController::class);

    // Attributions de classes
    Route::resource('assignments', AssignmentController::class);

    // Classes
    Route::get('classes', [ClasseController::class, 'index'])->name('classes.index');
    Route::get('classes/{classId}/students', [ClasseController::class, 'students'])->name('classes.students');
    Route::get('classes/{classId}/teachers', [ClasseController::class, 'teachers'])->name('classes.teachers');
    Route::get('classes/{classId}/timetable', [ClasseController::class, 'timetable'])->name('classes.timetable');
    Route::get('classes/{class}/students/pdf', [ClasseController::class, 'downloadStudentsPdf'])->name('classes.students.pdf');
    Route::get('classes/{class}/enseignants/export', [ClasseController::class, 'export'])->name('classes.teachers.export');

    // Timetables
    Route::get('classes/{classId}/timetables', [TimetableController::class, 'index'])->name('timetables.index');
    Route::post('classes/{classId}/timetables', [TimetableController::class, 'store'])->name('timetables.store');
    Route::get('classes/{classId}/timetables/{id}/edit', [TimetableController::class, 'edit'])->name('timetables.edit');
    Route::put('classes/{classId}/timetables/{id}', [TimetableController::class, 'update'])->name('timetables.update');
    Route::delete('classes/{classId}/timetables/{id}', [TimetableController::class, 'destroy'])->name('timetables.destroy');
    Route::get('classes/{class}/timetables/download', [TimetableController::class, 'downloadPDF'])->name('timetables.download');
});

//Enseignant  
Route::prefix('teacher')->name('teacher.')->middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Classes de l’enseignant
    Route::get('/classes', [TeacherClassController::class, 'index'])->name('classes');
    Route::get('/classes/{classId}/students', [TeacherClassController::class, 'students'])->name('classes.students');
    Route::get('/classes/{classId}/timetable', [TeacherClassController::class, 'timetable'])->name('classes.timetable');
});

// Auth routes

require __DIR__.'/auth.php';
