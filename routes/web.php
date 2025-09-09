<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SchoolController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\InvitationController;
use App\Http\Controllers\Auth\InvitationAcceptController;

Route::get('/', function(){ 
    return view('welcome'); 
})->name('home');



Route::middleware(['auth'])->prefix('admin')->group(function() {
    Route::resource('schools', SchoolController::class)->except(['show','edit','update'])
        ->middleware('can:manage schools');

    Route::resource('classes', ClassController::class)->except(['show','edit','update'])
        ->middleware('can:manage classes');

    Route::resource('invitations', InvitationController::class)->only(['index','store','destroy'])
        ->middleware('can:manage invitations');

    Route::post('invitations/{invitation}/resend', [InvitationController::class,'resend'])
        ->name('invitations.resend')
        ->middleware('can:manage invitations');
});


Route::get('/invitation/accept/{token}', [InvitationAcceptController::class,'showForm'])->name('invitation.accept');
Route::post('/invitation/accept/{token}', [InvitationAcceptController::class,'accept'])->name('invitation.accept.submit');

require __DIR__.'/auth.php';


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');
