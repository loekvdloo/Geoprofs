<?php

use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\VerlofBeoordelingController;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Role;
use App\Models\Afdeling;
use Inertia\Inertia;

// **Gasten routes**
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

// **Auth routes**
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/', fn() => Inertia::render('Dashboard'))->name('home');
    Route::get('/dashboard', fn() => Inertia::render('Dashboard'))->name('dashboard');

    // Logout
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    // Verlof overzicht (frontend)
    Route::get('/verlof/overzicht', fn() => Inertia::render('Verlof/AfdelingOverzicht'))
        ->name('verlof.overzicht');

    // Verlof Index
    Route::get('/verlof', fn() => Inertia::render('Verlof/Index'))->name('verlof.index');

    // Verlof agenda
    Route::get('/verlof/agenda', fn() => Inertia::render('Verlof/agenda'))->name('verlof.agenda');

    // Verlof beoordeling (frontend)
    Route::get('/verlof/beoordeling', fn() => Inertia::render('Verlof/beoordeling'))
        ->name('verlof.beoordeling');

    // Verlof aanvraag (frontend)
    Route::get('/verlof/aanvraag', fn() => Inertia::render('Verlof/aanvraag'))
        ->name('verlof.aanvraag');

    // Verlof bezetting
    Route::get('/verlof/bezetting', fn() => Inertia::render('Verlof/Bezetting'))->name('verlof.bezetting');

    // Admin: gebruikers
    Route::get('/admin/users', function () {
        abort_if(auth()->user()->role_id !== 1, 403);
        return Inertia::render('Admin/UsersIndex', [
            'users' => User::with(['role', 'afdeling'])->get(),
        ]);
    })->name('admin.users.index');

    // Gebruiker bewerken (rol + afdeling)

    // Admin: gebruiker bewerken
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

    // Admin: login attempts
    Route::get('/records', function () {
        abort_if(auth()->user()->role_id !== 1, 403);
        return Inertia::render('Admin/LoginAttempts');
    })->name('records.index');

    Route::middleware('auth')->group(function () {
        Route::get('/verlof/agenda', fn() => Inertia::render('Verlof/agenda'))
            ->name('verlof.agenda');
    });


    //bezettings niveau pagina
    Route::get('/verlof/bezetting', function () {
        $user = auth()->user();

        if (!$user || !in_array((int) $user->role_id, [1, 3], true)) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Verlof/Bezetting');
    })->name('verlof.bezetting');

});
// Profiel bewerken
Route::get('/profile/edit', fn() => Inertia::render('Profile/Edit'))->name('profile.edit');

// Bulk acties voor verlof
Route::post('/verlof/bulk-accept', [VerlofBeoordelingController::class, 'bulkAccept']);
Route::post('/verlof/bulk-reject', [VerlofBeoordelingController::class, 'bulkReject']);
Route::get('/profile/edit', fn() => Inertia::render('Profile/Edit'))
    ->name('profile.edit');
