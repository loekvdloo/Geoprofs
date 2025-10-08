<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VerlofaanvraagController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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
    Route::get('/verlof/aanvragen/nieuw', [VerlofaanvraagController::class, 'create'])->name('verlof.create');
    Route::post('/verlof/aanvragen', [VerlofaanvraagController::class, 'store'])->name('verlof.store');
});

require __DIR__.'/auth.php';
