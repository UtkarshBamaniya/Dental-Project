<?php

use App\Http\Controllers\AppointmentTypeController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\DentalPatientAppointmentController;
use App\Http\Controllers\MedicalDetailController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('common/get-medical-details', [CommonController::class, 'getMedicalDetails'])
        ->name('common.medical-details');
    Route::post('common/get-appointment-types', [CommonController::class, 'getAppointmentTypes'])
        ->name('common.appointment-types');

    Route::post('dental-patient-appointments/{patient}/follow-up', [DentalPatientAppointmentController::class, 'storeFollowUp'])
        ->name('dental-patient-appointments.follow-up');

    Route::resource('dental-patient-appointments', DentalPatientAppointmentController::class);
    Route::resource('medical-details', MedicalDetailController::class);
    Route::resource('appointment-type', AppointmentTypeController::class);
});

require __DIR__.'/auth.php';
