<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\AuditLoginController;
use Inertia\Inertia;

// Home / Dashboard
// Home / Dashboard (alleen ingelogd)
Route::middleware('auth')->group(function () {
    Route::get('/', fn () => Inertia::render('Dashboard'))->name('home');
    Route::get('/dashboard', fn () => Inertia::render('Dashboard'))->name('dashboard');

    // WEB (sessie) logout
    Route::post('/logout', [AuthController::class, 'webLogout'])->name('logout');
});

// Loginpagina + inloggen (alleen voor gasten)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'loginPage'])->name('login');
    Route::post('/login', [AuthController::class, 'webLogin'])->name('login.perform');
});


// Verlof-aanvraag pagina (frontend layout)
Route::get('/verlof/aanvraag', fn() => Inertia::render('Verlof/aanvraag'))
    ->name('verlof.aanvraag');

// Verlof-beoordeling pagina (frontend layout)
Route::get('/verlof/beoordeling', fn() => Inertia::render('Verlof/beoordeling'))
    ->name('verlof.beoordeling');
