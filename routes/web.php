<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SchoolController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\InvitationController;
use App\Http\Controllers\Auth\InvitationAcceptController;
use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\YearController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\SchoolClassController;

// Page d'accueil
Route::get('/', fn() => view('welcome'))->name('home');

// Auth routes
require __DIR__.'/auth.php';

// Routes protégées par auth + préfixe admin
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function() {

    // Profil
    Route::get('profile', [ProfileController::class,'show'])->name('profile.show');
    Route::post('profile', [ProfileController::class,'update'])->name('profile.update');

    // Gestion des années
    Route::resource('years', YearController::class);

    // Gestion des classes via SchoolClassController (CRUD simple)
    Route::get('classes', [SchoolClassController::class,'index'])->name('classes.index');
    Route::get('classes/create', [SchoolClassController::class,'create'])->name('classes.create');
    Route::post('classes', [SchoolClassController::class,'store'])->name('classes.store');

    // Gestion des écoles
    Route::resource('schools', SchoolController::class)->except(['show','edit','update'])
        ->middleware('can:manage schools');

    // Gestion des invitations
    Route::resource('invitations', InvitationController::class)->only(['index','store','destroy'])
        ->middleware('can:manage invitations');
    Route::post('invitations/{invitation}/resend', [InvitationController::class,'resend'])
        ->name('invitations.resend')
        ->middleware('can:manage invitations');

    // Gestion des années académiques (resource partielle)
    Route::resource('academic_years', AcademicYearController::class)->except(['show','edit','update','destroy']);
});

// Acceptation invitation
Route::get('/invitation/accept/{token}', [InvitationAcceptController::class,'showForm'])->name('invitation.accept');
Route::post('/invitation/accept/{token}', [InvitationAcceptController::class,'accept'])->name('invitation.accept.submit');

// Dashboard
Route::get('/dashboard', fn() => view('dashboard'))->middleware('auth')->name('dashboard');


Route::get('/admin/invitations/create', [InvitationController::class,'create'])->name('admin.invitations.create');
Route::post('/admin/invitations', [InvitationController::class,'store'])->name('admin.invitations.store');
