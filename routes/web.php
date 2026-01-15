<?php

use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\VerlofBeoordelingController;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Role;
use App\Models\Afdeling;
use Inertia\Inertia;

// Alleen voor ingelogden
Route::middleware('auth')->group(function () {
    Route::get('/', fn() => Inertia::render('Dashboard'))->name('home');
    Route::get('/dashboard', fn() => Inertia::render('Dashboard'))->name('dashboard');
    Route::get('/verlof', fn() => Inertia::render('Verlof/Index'))->name('verlof.index');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

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

    Route::middleware(['auth'])->get('/api/admin/users/{id}', function ($id) {
        $user = User::with(['role', 'afdeling'])->find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json($user);
    });
    
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

Route::middleware('auth')->group(function () {
    Route::post('/verlof/bulk-accept', [VerlofBeoordelingController::class, 'bulkAccept']);
    Route::post('/verlof/bulk-reject', [VerlofBeoordelingController::class, 'bulkReject']);
});
Route::get('/profile/edit', fn() => Inertia::render('Profile/Edit'))
    ->name('profile.edit');
