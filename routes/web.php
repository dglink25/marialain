<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvitationMail;
use App\Models\Invitation;

// Controllers
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InvitationResponseController;
use App\Http\Controllers\StudentPaymentController;


// Admin
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\InvitationController as AdminInvitationController;
use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\StudentExportController;
use App\Http\Controllers\Admin\StudentValidationController;

// Censeur
use App\Http\Controllers\Censeur\ClasseController;
use App\Http\Controllers\Censeur\InvitationController as CenseurInvitationController;
use App\Http\Controllers\Censeur\SubjectController;
use App\Http\Controllers\Censeur\AssignmentController;
use App\Http\Controllers\Censeur\TimetableController;

// Teacher
use App\Http\Controllers\Teacher\DashboardController;

use App\Http\Controllers\Teacher\ClassController as TeacherClassController;
use App\Http\Controllers\Admin\EntityController;

Route::get('/admin/entities/{entity}/classes', [EntityController::class, 'getClasses']);

use App\Http\Controllers\Dprimaire\ClassesprimaireController;
use App\Http\Controllers\Dprimaire\AjouterClasse;
use App\Http\Controllers\Dprimaire\primaryteacherController;




//Routes publiques

Route::get('/', fn() => view('accueil'))->name('accueil');
Route::get('/home', fn() => view('welcome'))->name('home');


// Test mail (à supprimer en prod)

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Page publique
Route::get('/home', function () {
    return view('welcome');
})->name('home');

//primaire
Route:: get('/primaire/classe/classes', [ClassesprimaireController::class, 'index'])-> name('primaire.classes');
Route:: get('/primaire/classe/ajouter', [AjouterClasse::class, 'index'])-> name('primaire.ajouterclasse');
Route:: get('/primaire/enseignants/enseignants', [primaryteacherController::class, 'index'])-> name('primaire.enseignants.enseignants');


Route::get('/', function () {
    return view('accueil');
})->name('accueil');

// Page classes primaires
Route::get('/admin/classes/primary/secondary_classes', function (){
    return view('admin.classes.secondary_classes');
})->name('admin.classes.primary');

Route::get('classes', [ClasseController::class, 'index'])->name('censeur.classes.index');

// Page classes secondaires
Route::get('/admin/classes/secondary/create', [App\Http\Controllers\Admin\ClasseController::class, 'createSecondary'])
    ->name('admin.classes.secondary');

// Profil
Route::middleware('auth')->group(function () {
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profil/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profil/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::post('/profil/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo');

    // Dashboards par rôle
    Route::get('/dashboard/directeur', function () {
        return view('dashboards.directeur', ['user' => auth()->user()]);
    })->name('directeur.dashboard');

    Route::get('/dashboard/censeur', function () {
        return view('dashboards.censeur', ['user' => auth()->user()]);
    })->name('censeur.dashboard');

    Route::get('/dashboard/surveillant', function () {
        return view('dashboards.surveillant', ['user' => auth()->user()]);
    })->name('surveillant.dashboard');

    Route::get('/dashboard/secretaire', function () {
        return view('dashboards.secretaire', ['user' => auth()->user()]);
    })->name('secretaire.dashboard');
});

//Validation inscription en attente 

Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('students/pending', [StudentValidationController::class, 'index'])
        ->name('admin.students.pending');
    Route::post('students/{student}/validate', [StudentValidationController::class, 'validateStudent'])
        ->name('admin.students.validate');
});

// Auth (Breeze fournit login/logout/password reset)
require __DIR__.'/auth.php';

// Zone Admin protégée
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminAuthController::class, 'index'])->name('dashboard');

    // Gestion des admins (création)
    Route::post('/admins', [AdminAuthController::class, 'createAdmin'])->name('admins.store');

    // Invitations
    Route::get('/invitations', [InvitationController::class, 'index'])->name('invitations.index');
    Route::post('/invitations', [InvitationController::class, 'store'])->name('invitations.store');

    // Classes
    Route::get('/classes', [ClassController::class, 'index'])->name('classes.index');
    Route::post('/classes', [ClassController::class, 'store'])->name('classes.store');
});

// Route de test pour invitation mail (supprimer en prod)

Route::get('/test-invitation-mail', function () {
    $inv = Invitation::first();
    if (!$inv) {
        return 'Aucune invitation en base pour test.';
    }
    Mail::to('test@example.com')->send(new InvitationMail($inv));
    return 'Mail d’invitation envoyé !';
});


// Invitation par token


Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminAuthController::class,'index'])->name('dashboard');

    // Années académiques
    Route::get('/academic-years', [AcademicYearController::class,'index'])->name('academic_years.index');
    Route::post('/academic-years', [AcademicYearController::class,'store'])->name('academic_years.store');

    // Classes
    Route::get('/classes', [ClassController::class,'index'])->name('classes.index');
    Route::post('/classes', [ClassController::class,'store'])->name('classes.store');

    // Invitations enseignants
    Route::get('/invitations', [InvitationController::class,'index'])->name('invitations.index');
    Route::post('/invitations', [InvitationController::class,'store'])->name('invitations.store');
});

Route::middleware(['auth'])->group(function () {
    //Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'index'])->name('dashboard');
});

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('classes', ClassController::class);
});

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('academic_years', AcademicYearController::class);
});



Route::prefix('admin')->group(function () {
    Route::get('students/create', [StudentController::class, 'create'])->name('admin.students.create');
    Route::post('students', [StudentController::class, 'store'])->name('admin.students.store');

    // Route pour récupérer les classes dynamiquement
    Route::get('entities/{entity}/classes', [StudentController::class, 'getClassesByEntity']);
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::resource('students', StudentController::class);
});


Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('students/list', [StudentExportController::class, 'index'])->name('students.list');
    Route::get('students/export/pdf', [StudentExportController::class, 'exportPdf'])->name('students.export.pdf');
    Route::get('students/export/excel', [StudentExportController::class, 'exportExcel'])->name('students.export.excel');
});



Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/students/list', [StudentController::class, 'listAlphabetical'])
         ->name('students.list');
});


// Routes censeur (auth requis mais pas de middleware global 'role' dans Kernel)
Route::prefix('censeur')->name('censeur.')->middleware('auth')->group(function () {
    Route::get('/invitations', [CenseurInvitationController::class, 'index'])->name('invitations.index');
    Route::resource('subjects', SubjectController::class);
    Route::resource('assignments',AssignmentController::class);
    //Route::resource('timetables',TimetableController::class);
    Route::post('/invitations', [CenseurInvitationController::class, 'send'])->name('invitations.send');
});

Route::prefix('censeur')->group(function () {
    Route::get('timetables/{classId}', [TimetableController::class, 'index'])
        ->name('censeur.timetables.index');
});


Route::prefix('teacher')->middleware(['auth'])->name('teacher.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Liste des classes de l’enseignant
    Route::get('/classes', [TeacherClassController::class, 'index'])->name('classes');

    // Liste des élèves dans une classe
    Route::get('/classes/{classId}/students', [TeacherClassController::class, 'students'])
        ->name('classes.students');

    // Emploi du temps d’une classe
    Route::get('/classes/{classId}/timetable', [TeacherClassController::class, 'timetable'])
        ->name('classes.timetable');
});


Route::prefix('censeur')->group(function () {
    Route::get('classes', [ClasseController::class, 'index'])->name('censeur.classes.index');

    // Actions par classe
    Route::get('classes/{classId}/students', [ClasseController::class, 'students'])->name('censeur.classes.students');
    Route::get('classes/{classId}/timetable', [ClasseController::class, 'timetable'])->name('censeur.classes.timetable');
    Route::get('classes/{classId}/teachers', [ClasseController::class, 'teachers'])->name('censeur.classes.teachers');
});



Route::prefix('censeur')->group(function () {
    // Timetables
    Route::get('classes/{classId}/timetables', [TimetableController::class, 'index'])->name('censeur.timetables.index');
    Route::post('classes/{classId}/timetables', [TimetableController::class, 'store'])->name('censeur.timetables.store');
    Route::put('classes/{classId}/timetables/{id}', [TimetableController::class, 'update'])->name('censeur.timetables.update');
    Route::delete('classes/{classId}/timetables/{id}', [TimetableController::class, 'destroy'])->name('censeur.timetables.destroy');
});

Route::get('/invitation/accept/{token}', [InvitationResponseController::class, 'accept'])
    ->name('invitation.accept');

//Profil (utilisateurs connectés)

Route::middleware('auth')->group(function () {

    // Édition du profil
    Route::get('/profil/modifier', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profil/modifier', [ProfileController::class, 'update'])->name('profile.update');

    // Changement de mot de passe
    Route::post('/profil/mot-de-passe', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Mise à jour de la photo de profil
    Route::post('/profil/photo', [ProfileController::class, 'updatePhoto'])->name('profile.updatePhoto');
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

// Admin Sécreateire pour valider inscription

Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('students/pending', [StudentValidationController::class, 'index'])
        ->name('admin.students.pending');
    Route::post('students/{student}/validate', [StudentValidationController::class, 'validateStudent'])
        ->name('admin.students.validate');
});

// Admin

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminAuthController::class, 'index'])->name('dashboard');
    Route::post('/admins', [AdminAuthController::class, 'createAdmin'])->name('admins.store');

    // Invitations enseignants
    Route::resource('invitations', AdminInvitationController::class)->only(['index', 'store']);

    // Classes
    Route::resource('classes', ClassController::class)->only(['index', 'edit', 'update', 'store', 'create', 'show', 'destroy']);

    // Années académiques
    Route::resource('academic_years', AcademicYearController::class)->only(['index', 'store', 'create']);

    // Étudiants
    Route::resource('students', StudentController::class);
    Route::get('students/list', [StudentController::class, 'listAlphabetical'])->name('students.list');
    Route::get('students/export/pdf', [StudentController::class, 'exportPdf'])->name('students.export.pdf');
    Route::get('students/export/excel', [StudentExportController::class, 'exportExcel'])->name('students.export.excel');
    Route::get('admin/students/export/all-pdf', [App\Http\Controllers\Admin\StudentController::class, 'exportAllPdf'])
        ->name('students.export.all.pdf');
});

//Inscription public 
Route::get('/inscription', [StudentController::class, 'inscription'])->name('students.create');
Route::post('/inscription', [StudentController::class, 'store'])->name('students.store');


// Censeur

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

    // Timetables
    Route::get('classes/{classId}/timetables', [TimetableController::class, 'index'])->name('timetables.index');
    Route::post('classes/{classId}/timetables', [TimetableController::class, 'store'])->name('timetables.store');
    Route::get('classes/{classId}/timetables/{id}/edit', [TimetableController::class, 'edit'])->name('timetables.edit');
    Route::put('classes/{classId}/timetables/{id}', [TimetableController::class, 'update'])->name('timetables.update');
    Route::delete('classes/{classId}/timetables/{id}', [TimetableController::class, 'destroy'])->name('timetables.destroy');
    Route::get('classes/{class}/timetables/download', [TimetableController::class, 'downloadPDF'])->name('timetables.download');
});

// Afficher le profil d’un enseignant
Route::get('/enseignants/{user}', [App\Http\Controllers\ProfileController::class, 'show'])
    ->name('enseignants.show');

// Export PDF des enseignants d’une classe (Censeur)
Route::get('/classes/{class}/enseignants/export', [App\Http\Controllers\Censeur\ClasseController::class, 'export'])
    ->name('enseignants.export');

//Enseignant  
Route::prefix('teacher')->name('teacher.')->middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Classes de l’enseignant
    Route::get('/classes', [TeacherClassController::class, 'index'])->name('classes');
    Route::get('/classes/{classId}/students', [TeacherClassController::class, 'students'])->name('classes.students');
    Route::get('/classes/{classId}/timetable', [TeacherClassController::class, 'timetable'])->name('classes.timetable');
});

Route::prefix('students')->name('students.')->group(function() {
    Route::get('{student}/payments', [StudentPaymentController::class,'index'])->name('payments.index');
    Route::get('{student}/payments/create', [StudentPaymentController::class,'create'])->name('payments.create');
    Route::post('{student}/payments', [StudentPaymentController::class,'store'])->name('payments.store');
});

Route::get('subjects/{subject}/teachers', [SubjectController::class, 'teachers'])
    ->name('subjects.teachers');