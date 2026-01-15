<?php

use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\VerlofBeoordelingController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Routes voor gasten (niet ingelogd)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

/*
|--------------------------------------------------------------------------
| Routes voor ingelogde gebruikers
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Dashboard / Home
    Route::get('/', fn () => Inertia::render('Dashboard'))->name('home');
    Route::get('/dashboard', fn () => Inertia::render('Dashboard'))->name('dashboard');

    // Verlof pages (frontend)
    Route::get('/verlof', fn () => Inertia::render('Verlof/Index'))->name('verlof.index');
    Route::get('/verlof/aanvraag', fn () => Inertia::render('Verlof/aanvraag'))->name('verlof.aanvraag');
    Route::get('/verlof/beoordeling', fn () => Inertia::render('Verlof/beoordeling'))->name('verlof.beoordeling');

    // Verlof acties (bulk)
    Route::post('/verlof/bulk-accept', [VerlofBeoordelingController::class, 'bulkAccept'])
        ->name('verlof.bulkAccept');
    Route::post('/verlof/bulk-reject', [VerlofBeoordelingController::class, 'bulkReject'])
        ->name('verlof.bulkReject');

    // Profile
    Route::get('/profile/edit', fn () => Inertia::render('Profile/Edit'))->name('profile.edit');

    // Admin-only page (zelfde stijl als jouw originele /records)
    Route::get('/records', function () {
        $user = auth()->user();

        if (!$user || (int) $user->role_id !== 1) {
            abort(403);
        }

        return Inertia::render('Admin/LoginAttempts');
    })->name('records.index');

 Route::middleware('auth')->group(function () {
    Route::get('/verlof/agenda', fn () => Inertia::render('Verlof/agenda'))
        ->name('verlof.agenda');
});


    // Logout
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    

    
});
