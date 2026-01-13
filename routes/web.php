<?php

use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\VerlofBeoordelingController;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Role;
use App\Models\Afdeling;
use Inertia\Inertia;

// ================================
// Alleen voor ingelogde gebruikers
// ================================
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/', fn() => Inertia::render('Dashboard'))->name('home');
    Route::get('/dashboard', fn() => Inertia::render('Dashboard'))->name('dashboard');

    // Verlof
    Route::get('/verlof', fn() => Inertia::render('Verlof/Index'))->name('verlof.index');
    Route::get('/verlof/aanvraag', fn() => Inertia::render('Verlof/aanvraag'))->name('verlof.aanvraag');
    Route::get('/verlof/beoordeling', fn() => Inertia::render('Verlof/beoordeling'))->name('verlof.beoordeling');

    // Bulk acties
    Route::post('/verlof/bulk-accept', [VerlofBeoordelingController::class, 'bulkAccept']);
    Route::post('/verlof/bulk-reject', [VerlofBeoordelingController::class, 'bulkReject']);

    // Profiel
    Route::get('/profile/edit', fn() => Inertia::render('Profile/Edit'))->name('profile.edit');

    // Logout
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // ================================
    // Admin routes (alleen role_id = 1)
    // ================================

    // Gebruikerslijst
    Route::get('/admin/users', function () {
        abort_if(auth()->user()->role_id !== 1, 403);

        return Inertia::render('Admin/UsersIndex', [
            'users' => User::with(['role', 'afdeling'])->get(),
        ]);
    })->name('admin.users.index');

    // Gebruiker bewerken (rol + afdeling)
    Route::get('/admin/users/{user}/edit', function (User $user) {
        abort_if(auth()->user()->role_id !== 1, 403);

        return Inertia::render('Admin/UserRoleEdit', [
            'user' => $user,
            'roles' => Role::all(),
            'afdelingen' => Afdeling::all(),
        ]);
    })->name('admin.users.edit');

    // Records bekijken
    Route::get('/records', function () {
        $user = auth()->user();
        if (!$user || (int) $user->role_id !== 1) {
            abort(403);
        }
        return Inertia::render('Admin/LoginAttempts');
    })->name('records.index');
});

// ================================
// Alleen voor gasten
// ================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});
