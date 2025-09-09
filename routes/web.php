<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SchoolController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\InvitationController;
use App\Http\Controllers\Auth\InvitationAcceptController;

Route::get('/', function(){ return view('welcome'); })->name('home');

Route::middleware(['auth','role:founder|admin'])->prefix('admin')->group(function(){
    Route::resource('schools', SchoolController::class)->except(['show','edit','update']);
    Route::resource('classes', ClassController::class)->except(['show','edit','update']);
    Route::resource('invitations', InvitationController::class)->only(['index','store','destroy']);
    Route::post('invitations/{invitation}/resend', [InvitationController::class,'resend'])->name('invitations.resend');
});

Route::get('/invitation/accept/{token}', [InvitationAcceptController::class,'showForm'])->name('invitation.accept');
Route::post('/invitation/accept/{token}', [InvitationAcceptController::class,'accept'])->name('invitation.accept.submit');

require __DIR__.'/auth.php';




Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');
