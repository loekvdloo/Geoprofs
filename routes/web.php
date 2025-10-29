<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VerlofaanvraagController;
use App\Models\Verloftype;

use App\Http\Controllers\VerlofBeoordelingController;


use Inertia\Inertia;


// Home redirect
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Loginpagina alleen voor gasten
Route::get('login', [AuthController::class, 'loginPage'])
    ->name('login')
    ->middleware('guest');

// Logout route (altijd beschikbaar voor ingelogden)
Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth'); // alleen ingelogd kan uitloggen


// Dashboard en andere beveiligde pagina's alleen voor ingelogden
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::middleware('auth')->group(function () {
        Route::get('/verlof/aanvraag', function () {
            return Inertia::render('Verlof/aanvraag', [
                'types' => Verloftype::orderBy('naam')->get(['verlof_type_id', 'naam', 'betaald']),
            ]);
        });
        Route::post('/verlof/aanvragen', [VerlofaanvraagController::class, 'store'])->name('verlof.store');
        Route::get('/verlof/beoordeling', [VerlofBeoordelingController::class, 'index'])->name('verlof.beoordeling');
        Route::post('/verlof/beoordeling/{aanvraag}/accept', [VerlofBeoordelingController::class, 'accept']);
        Route::post('/verlof/beoordeling/{aanvraag}/reject', [VerlofBeoordelingController::class, 'reject']);

        Route::get('/verlof/aanvraag', function () {
            return Inertia::render('Verlof/aanvraag', [
                'types' => Verloftype::orderBy('naam')
                    ->get(['verlof_type_id', 'naam', 'betaald']),
            ]);
        })->name('verlof.aanvraag');

        Route::post('/verlof/aanvragen', [VerlofaanvraagController::class, 'store'])
            ->name('verlof.store');
    });

});