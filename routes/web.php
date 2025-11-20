<?php

use App\Http\Controllers\Web\Auth\LoginController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Alleen voor ingelogden
Route::middleware('auth')->group(function () {
    Route::get('/', fn () => Inertia::render('Dashboard'))->name('home');
    Route::get('/dashboard', fn () => Inertia::render('Dashboard'))->name('dashboard');
    Route::get('/verlof', fn () => Inertia::render('Verlof/Index'))->name('verlof.index');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/records', function () {
        $user = auth()->user();

        if (!$user || (int) $user->role_id !== 1) {
            abort(403);
        }

        return Inertia::render('Admin/LoginAttempts');
    })->name('records.index');
});

// Alleen voor gasten
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});


// Verlof-aanvraag pagina (frontend layout)
Route::get('/verlof/aanvraag', fn() => Inertia::render('Verlof/aanvraag'))
    ->name('verlof.aanvraag');

// Verlof-beoordeling pagina (frontend layout)
Route::get('/verlof/beoordeling', fn() => Inertia::render('Verlof/beoordeling'))
    ->name('verlof.beoordeling');

Route::get('/profile/edit', fn() => Inertia::render('Profile/Edit'))
    ->name('profile.edit');
