<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvitationMail;
use App\Models\Invitation;

// Controllers généraux
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InvitationResponseController;
use App\Http\Controllers\StudentPaymentController;
use App\Http\Controllers\ArchiveController;

// Admin
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\InvitationController as AdminInvitationController;
use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\StudentExportController;
use App\Http\Controllers\Admin\StudentValidationController;
use App\Http\Controllers\Admin\EntityController;

// Censeur
use App\Http\Controllers\Censeur\ClasseController;
use App\Http\Controllers\Censeur\InvitationController as CenseurInvitationController;
use App\Http\Controllers\Censeur\SubjectController;
use App\Http\Controllers\Censeur\AssignmentController;
use App\Http\Controllers\Censeur\TimetableController;

// Teacher
use App\Http\Controllers\Teacher\DashboardController;
use App\Http\Controllers\Teacher\ClassController as TeacherClassController;

// Primaire
use App\Http\Controllers\Dprimaire\ClassesprimaireController;
use App\Http\Controllers\Dprimaire\primaryteacherController;




use App\Http\Controllers\Dprimaire\InvitationPController;
use App\Http\Controllers\Dprimaire\StudentsController;




/*
|--------------------------------------------------------------------------
| Routes publiques
|--------------------------------------------------------------------------
*/


Route::get('/admin/entities/{entity}/classes', [EntityController::class, 'getClasses']);

//primaire
Route:: get('/primaire/classe/classes', [ClassesprimaireController::class, 'index'])-> name('primaire.classe.classes');
Route:: post('/primaire/classe/classes', [ClassesprimaireController::class, 'store'])-> name('primaire.classe.store');
Route:: get('/primaire/classe/showclass/{id}', [ClassesprimaireController::class, 'show'])-> name('primaire.classe.showclass');
Route:: get('/primaire/enseignants/enseignants', [primaryteacherController::class, 'index'])-> name('primaire.enseignants.enseignants');
Route:: get('/primaire/enseignants/inviter', [InvitationPController::class, 'index'])-> name('primaire.enseignants.inviter');
Route::post('/primaire/enseignants/inviter', [InvitationPController::class, 'store'])-> name('primaire.enseignants.inviter.store');
Route:: get('/primaire/ecoliers/liste', [StudentsController::class, 'index'])-> name('primaire.ecoliers.liste');
Route::get('/primaire/ecoliers/pdf', [StudentsController::class, 'downloadPrimaireStudents'])
    ->name('primaire.ecoliers.liste.pdf');
Route::get('/', function () {
    return view('accueil');
})->name('accueil');

Route::get('/', fn() => view('accueil'))->name('accueil');
Route::get('/home', fn() => view('welcome'))->name('home');


// Page classes primaires
Route::get('/admin/classes/primary/secondary_classes', function () {
    return view('admin.classes.secondary_classes');
})->name('admin.classes.primary');

// Invitation acceptation
Route::get('/invitation/accept/{token}', [InvitationResponseController::class, 'accept'])->name('invitation.accept');

// Test mail (à supprimer en prod)
Route::get('/test-invitation-mail', function () {
    $inv = Invitation::first();
    if (!$inv) {
        return 'Aucune invitation en base pour test.';
    }
    Mail::to('test@example.com')->send(new InvitationMail($inv));
    return 'Mail d’invitation envoyé !';
});


/*
|--------------------------------------------------------------------------
| Primaire
|--------------------------------------------------------------------------
*/
Route::prefix('primaire')->name('primaire.')->group(function () {
    Route::get('classe/classes', [ClassesprimaireController::class, 'index'])->name('classe.classes');
    Route::post('classe/classes', [ClassesprimaireController::class, 'store'])->name('classe.store');
    Route::get('classe/showclass/{id}', [ClassesprimaireController::class, 'show'])->name('classe.showclass');

    Route::get('enseignants/enseignants', [primaryteacherController::class, 'index'])->name('enseignants.enseignants');
    Route::get('enseignants/inviter', [InvitationPController::class, 'index'])->name('enseignants.inviter');
    Route::post('enseignants/inviter', [InvitationPController::class, 'store'])->name('enseignants.inviter.store');

    Route::get('ecoliers/liste', [StudentsController::class, 'index'])->name('ecoliers.liste');
    Route::get('ecoliers/pdf', [StudentsController::class, 'downloadPrimaireStudents'])->name('ecoliers.liste.pdf');
});


/*
|--------------------------------------------------------------------------
| Auth & Profils
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';

Route::middleware('auth')->group(function () {
    // Profil
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profil/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profil/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profil/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo');

    // Dashboards par rôle
    Route::get('/dashboard/directeur', fn() => view('dashboards.directeur', ['user' => auth()->user()]))->name('directeur.dashboard');
    Route::get('/dashboard/censeur', fn() => view('dashboards.censeur', ['user' => auth()->user()]))->name('censeur.dashboard');
    Route::get('/dashboard/surveillant', fn() => view('dashboards.surveillant', ['user' => auth()->user()]))->name('surveillant.dashboard');
    Route::get('/dashboard/secretaire', fn() => view('dashboards.secretaire', ['user' => auth()->user()]))->name('secretaire.dashboard');
});

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('students/pending', [StudentController::class, 'pending'])
        ->name('students.pending');
});


/*
|--------------------------------------------------------------------------
| Zone Admin (protégée)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminAuthController::class, 'index'])->name('dashboard');
    Route::post('/admins', [AdminAuthController::class, 'createAdmin'])->name('admins.store');

    // Entités
    Route::get('entities/{entity}/classes', [EntityController::class, 'getClasses']);

    // Invitations enseignants
    Route::resource('invitations', AdminInvitationController::class)->only(['index', 'store']);

    // Années académiques
    Route::resource('academic_years', AcademicYearController::class)->only(['index', 'store', 'create', 'edit', 'destroy', 'update']);

    // Classes
    Route::resource('classes', ClassController::class);

    // Étudiants
    Route::resource('students', StudentController::class);
    
    Route::post('students/{student}/validate', [StudentValidationController::class, 'validateStudent'])->name('students.validate');
    Route::get('students/list', [StudentController::class, 'listAlphabetical'])->name('students.list');

    // Exports
    Route::get('students/export/pdf', [StudentController::class, 'exportPdf'])->name('students.export.pdf');
    Route::get('students/export/pdf', [StudentExportController::class, 'exportPdf'])->name('students.export.pdf');
    Route::get('students/export/excel', [StudentExportController::class, 'exportExcel'])->name('students.export.excel');
    Route::get('students/export/all-pdf', [StudentController::class, 'exportAllPdf'])->name('students.export.all.pdf');

    //Validation inscription en attente 
    Route::get('students/pending', [StudentValidationController::class, 'index'])
        ->name('students.pending');
    Route::post('students/{student}/validate', [StudentValidationController::class, 'validateStudent'])
        ->name('students.validate');

});

/*
|--------------------------------------------------------------------------
| Inscription publique
|--------------------------------------------------------------------------
*/
Route::get('/inscription', [StudentController::class, 'inscription'])->name('students.create');
Route::post('/inscription', [StudentController::class, 'store'])->name('students.store');


/*
|--------------------------------------------------------------------------
| Zone Censeur
|--------------------------------------------------------------------------
*/
Route::prefix('censeur')->name('censeur.')->middleware('auth')->group(function () {
    // Invitations enseignants
    Route::get('/invitations', [CenseurInvitationController::class, 'index'])->name('invitations.index');
    Route::post('/invitations', [CenseurInvitationController::class, 'send'])->name('invitations.send');

    // Matières
    Route::resource('subjects', SubjectController::class);

    // Classes
    Route::get('classes', [ClasseController::class, 'index'])->name('classes.index');
    Route::get('classes/{classId}/students', [ClasseController::class, 'students'])->name('classes.students');
    Route::get('classes/{classId}/teachers', [ClasseController::class, 'teachers'])->name('classes.teachers');
    Route::get('classes/{classId}/timetable', [ClasseController::class, 'timetable'])->name('classes.timetable');
    Route::get('classes/{class}/students/pdf', [ClasseController::class, 'downloadStudentsPdf'])->name('classes.students.pdf');
    Route::get('classes/{class}/enseignants/export', [ClasseController::class, 'export'])->name('classes.teachers.export');

    // Emplois du temps
    Route::resource('assignments', AssignmentController::class);
    Route::get('classes/{classId}/timetables', [TimetableController::class, 'index'])->name('timetables.index');
    Route::post('classes/{classId}/timetables', [TimetableController::class, 'store'])->name('timetables.store');
    Route::get('classes/{classId}/timetables/{id}/edit', [TimetableController::class, 'edit'])->name('timetables.edit');
    Route::put('classes/{classId}/timetables/{id}', [TimetableController::class, 'update'])->name('timetables.update');
    Route::delete('classes/{classId}/timetables/{id}', [TimetableController::class, 'destroy'])->name('timetables.destroy');
    Route::get('classes/{class}/timetables/download', [TimetableController::class, 'downloadPDF'])->name('timetables.download');
});


/*
|--------------------------------------------------------------------------
| Zone Enseignant
|--------------------------------------------------------------------------
*/
Route::prefix('teacher')->name('teacher.')->middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Classes de l’enseignant
    Route::get('/classes', [TeacherClassController::class, 'index'])->name('classes');
    Route::get('/classes/{classId}/students', [TeacherClassController::class, 'students'])->name('classes.students');
    Route::get('/classes/{classId}/timetable', [TeacherClassController::class, 'timetable'])->name('classes.timetable');
});


/*
|--------------------------------------------------------------------------
| Étudiants - Paiements
|--------------------------------------------------------------------------
*/
Route::prefix('students')->name('students.')->group(function () {
    Route::get('{student}/payments', [StudentPaymentController::class,'index'])->name('payments.index');
    Route::get('{student}/payments/create', [StudentPaymentController::class,'create'])->name('payments.create');
    Route::post('{student}/payments', [StudentPaymentController::class,'store'])->name('payments.store');
});


/*
|--------------------------------------------------------------------------
| Archives
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::get('/archives', [ArchiveController::class, 'index'])->name('archives.index');
    Route::get('/archives/{id}', [ArchiveController::class, 'show'])->name('archives.show');
});


/*
|--------------------------------------------------------------------------
| Divers
|--------------------------------------------------------------------------
*/
// Afficher le profil d’un enseignant
Route::get('/enseignants/{user}', [ProfileController::class, 'show'])->name('enseignants.show');

// Export PDF des enseignants d’une classe (Censeur)
Route::get('/classes/{class}/enseignants/export', [ClasseController::class, 'export'])->name('enseignants.export');

// Sujet -> enseignants
Route::get('subjects/{subject}/teachers', [SubjectController::class, 'teachers'])->name('subjects.teachers');
