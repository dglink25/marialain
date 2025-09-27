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
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\DashboardPrimaireController;

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

use App\Http\Controllers\SecretaryDashboardController;
use App\Http\Controllers\Dprimaire\InvitationPController;
use App\Http\Controllers\Dprimaire\StudentsController;
use App\Http\Controllers\StudentMailController;
use App\Http\Controllers\CenseurDashboardController;
use App\Http\Controllers\SurveillantController;
use App\Http\Controllers\Teacher\PrimaireClasseController;
use App\Http\Controllers\Teacher\PrimaireSubjectController;

use App\Http\Controllers\Teacher\PrimaireScheduleController;
use App\Http\Controllers\Teacher\NoteController;
use App\Http\Controllers\Teacher\GradeController;

/*
|--------------------------------------------------------------------------
| Routes publiques
|--------------------------------------------------------------------------
*/


Route::get('/', fn() => view('accueil'))->name('accueil');
//Route::get('/home', fn() => view('welcome'))->name('home');

Route::get('/home', [WelcomeController::class, 'index'])->name('home');



Route::get('/admin/entities/{entity}/classes', [EntityController::class, 'getClasses']);

//primaire
Route:: get('/primaire/classe/classes', [ClassesprimaireController::class, 'index'])-> name('primaire.classe.classes');
Route:: post('/primaire/classe/classes', [ClassesprimaireController::class, 'store'])-> name('primaire.classe.store');
Route:: get('/primaire/classe/showclass/{id}', [ClassesprimaireController::class, 'show'])-> name('primaire.classe.showclass');
Route:: get('/primaire/enseignants/enseignants', [primaryteacherController::class, 'index'])-> name('primaire.enseignants.enseignants');
Route:: get('/primaire/ecoliers/liste', [StudentsController::class, 'index'])-> name('primaire.ecoliers.liste');
Route::get('/primaire/ecoliers/pdf', [StudentsController::class, 'downloadPrimaireStudents'])
    ->name('primaire.ecoliers.liste.pdf');
Route::get('/primaire/classe/{id}/pdf', [ClassesprimaireController::class, 'downloadClassStudents'])-> name('primaire.classe.pdf');
Route::get('/primaire/enseignants/pdf', [primaryteacherController::class, 'downloadTeachersList'])->name('primaire.enseignants.pdf');
Route::get('/primaire/enseignants/{id}/show', [primaryteacherController::class, 'show'])-> name('primaire.enseignants.show');
Route::get('/', function () {
    return view('accueil');
})->name('accueil');
Route:: get('/primaire/ecoliers/{id}/show', [StudentsController::class, 'show'])-> name('primaire.ecoliers.show');

Route::prefix('primaire/enseignants')->name('primaire.enseignants.')->group(function () {
    Route::get('/', [InvitationPController::class, 'index'])->name('index');
    Route::post('/send', [InvitationPController::class, 'send'])->name('send');
    Route::get('/accept/{token}', [InvitationPController::class, 'accept'])->name('accept');
});

//enseignants primaires
Route::middleware(['auth'])
    ->prefix('teacher/primaire')
    ->name('teacher.')
    ->group(function () {
        Route::get('/subjects', [PrimaireSubjectController::class, 'index'])->name('subjects.primaire');
        Route::post('/subjects', [PrimaireSubjectController::class, 'store'])->name('subjects.store');
        Route::put('/subjects/{subject}', [PrimaireSubjectController::class, 'update'])->name('subjects.update');
        Route::delete('/subjects/{subject}', [PrimaireSubjectController::class, 'destroy'])->name('subjects.destroy');
    });
Route::middleware(['auth'])->prefix('teacher/primaire')->name('teacher.')->group(function () {
    Route::get('/classes', [PrimaireClasseController::class, 'index'])->name('classes.primaire');
    Route::get('/subjects', [PrimaireSubjectController::class, 'index'])->name('subjects.primaire');
});


Route::get('/', fn() => view('accueil'))->name('accueil');
//Route::get('/home', fn() => view('welcome'))->name('home');



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
Route::get('teacher/primaire/schedules/download', [PrimaireScheduleController::class, 'downloadPdf'])
     ->name('schedules.download');
// Page pour voir l'emploi du temps d'une classe (directeur)
Route::get('teacher/primaire/schedules/{classe}', [PrimaireScheduleController::class, 'directeur'])
     ->name('schedules.ind');

Route::prefix('teacher/primaire')->middleware('auth')->group(function () {
    Route::resource('schedules', \App\Http\Controllers\Teacher\PrimaireScheduleController::class);
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

    Route::get('/dashboard/directeur', [DashboardPrimaireController::class, 'index'])->name('directeur.dashboard');

    Route::get('/dashboard/surveillant', fn() => view('dashboards.surveillant', ['user' => auth()->user()]))->name('surveillant.dashboard');
    
    Route::get('dashboard', [CenseurDashboardController::class, 'index'])->name('censeur.dashboard');

});

//Dashboards Secretaraire

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard/secretary', [SecretaryDashboardController::class, 'index'])
        ->name('secretaire.dashboard');
    Route::get('/students/unpaid', [SecretaryDashboardController::class, 'unpaidStudents'])
    ->name('students.unpaid');
    Route::post('/students/unpaid/send-mails', [StudentMailController::class, 'sendToAll'])->name('students.mail.sendAll');
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
    Route::get('/home', [AdminAuthController::class, 'accueil'])->name('accueil');
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
    Route::get('students/export/excel', [StudentExportController::class, 'exportExcel'])->name('students.export.excel');
    Route::get('students/export/all-pdf', [StudentController::class, 'exportAllPdf'])->name('students.export.all.pdf');

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
    Route::get('/{year}/classes/{class}', [ArchiveController::class, 'classStudents'])->name('archives.classes.students');
    Route::get('/{year}/{class}/timetables', [ArchiveController::class, 'classTimetables'])
        ->name('archives.class_timetables');
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


// Surveillant
Route::middleware(['auth'])->prefix('surveillant')->group(function () {
    // Liste des classes
    Route::get('/classes', [SurveillantController::class, 'classesList'])->name('surveillant.classes');

    // Attribuer conduite à une classe
    Route::post('/classes/{id}/conducts', [SurveillantController::class, 'assignConducts'])->name('surveillant.classes.conducts');

    // Voir élèves d'une classe
    Route::get('/classes/{id}/students', [SurveillantController::class, 'classStudents'])->name('surveillant.classes.students');

    // Punir un élève
    Route::post('/students/{id}/punish', [SurveillantController::class, 'punish'])->name('surveillant.students.punish');

    // Historique des punitions d’un élève
    Route::get('/students/{id}/punishments', [SurveillantController::class, 'punishmentsHistory'])->name('surveillant.students.history');

});

/*

Gestion notes côté enseignants

*/

Route::get('classes/{class}/notes', [NoteController::class, 'showClassNotes'])->name('teacher.classes.notes.list');

Route::middleware(['auth'])->prefix('teacher')->name('teacher.')->group(function () {

    // Notes
    Route::get('/classes/{id}/notes', [App\Http\Controllers\Teacher\NoteController::class, 'index'])->name('classes.notes');
    
    // Insertion des notes
    Route::get('/classes/{id}/notes/{type}/{num}/create', [App\Http\Controllers\Teacher\NoteController::class, 'create'])->name('classes.notes.create');
    Route::post('/classes/{id}/notes/{type}/{num}', [App\Http\Controllers\Teacher\NoteController::class, 'store'])->name('classes.notes.store');
    Route::get('classes/{class}/notes/read/{type}/{num}', [App\Http\Controllers\Teacher\GradeController::class, 'read'])->name('classes.notes.read');
    // Calcul des moyennes
    Route::post('/classes/{id}/notes/calc/interrogations', [App\Http\Controllers\Teacher\NoteController::class, 'calcInterro'])->name('classes.notes.calc.interro');
    Route::post('/classes/{id}/notes/calc/trimestre', [App\Http\Controllers\Teacher\NoteController::class, 'calcTrimestre'])->name('classes.notes.calc.trimestre');
});


