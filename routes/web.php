<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\InvitationController;
use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\ClassController;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvitationMail;
use App\Models\Invitation;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\StudentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Page publique
Route::get('/', function () {
    return view('welcome');
})->name('home');

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
    if (! $inv) {
        return 'Aucune invitation en base pour test.';
    }
    Mail::to('test@example.com')->send(new InvitationMail($inv));
    return 'Mail d’invitation envoyé !';
});


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

use App\Http\Controllers\Admin\StudentExportController;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('students/list', [StudentExportController::class, 'index'])->name('students.list');
    Route::get('students/export/pdf', [StudentExportController::class, 'exportPdf'])->name('students.export.pdf');
    Route::get('students/export/excel', [StudentExportController::class, 'exportExcel'])->name('students.export.excel');
});



Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    // ... tes autres routes
    
    Route::get('/students/list', [StudentController::class, 'listAlphabetical'])
         ->name('students.list');
});