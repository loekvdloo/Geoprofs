<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VerlofaanvraagController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\Verloftype;
use App\Http\Controllers\VerlofBeoordelingController;



Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/verlof/aanvraag', function () {
        return Inertia::render('Verlof/aanvraag', [
            'types' => Verloftype::orderBy('naam')->get(['verlof_type_id','naam','betaald']),
        ]);
    });    Route::post('/verlof/aanvragen', [VerlofaanvraagController::class, 'store'])->name('verlof.store');
     Route::get('/verlof/beoordeling', [VerlofBeoordelingController::class, 'index'])->name('verlof.beoordeling');
    Route::post('/verlof/beoordeling/{aanvraag}/accept', [VerlofBeoordelingController::class, 'accept']);
    Route::post('/verlof/beoordeling/{aanvraag}/reject', [VerlofBeoordelingController::class, 'reject']);
});

require __DIR__.'/auth.php';
