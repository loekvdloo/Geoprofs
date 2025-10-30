<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Inertia\Inertia;

// Home / Dashboard
Route::get('/', fn() => Inertia::render('Dashboard'));
Route::get('/dashboard', fn() => Inertia::render('Dashboard'));

// Loginpagina alleen voor gasten
Route::get('login', [AuthController::class, 'loginPage'])
    ->name('login')
    ->middleware('guest');

// Logout via API (token-based)
Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout');

// Verlof-aanvraag pagina (frontend layout)
Route::get('/verlof/aanvraag', fn() => Inertia::render('Verlof/aanvraag'))
    ->name('verlof.aanvraag');

// Verlof-beoordeling pagina (frontend layout)
Route::get('/verlof/beoordeling', fn() => Inertia::render('Verlof/beoordeling'))
    ->name('verlof.beoordeling');
