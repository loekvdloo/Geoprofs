<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\VerlofaanvraagController;
use App\Http\Controllers\VerlofBeoordelingController;
use App\Models\Verloftype;
use Illuminate\Support\Facades\Route;

// Login - registreren via API
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'apiLogin']);


// Alleen beschermd via Bearer-token
Route::middleware('auth:sanctum')->group(function () {

    // Logout en user info
    Route::get('/user',    [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Verloftypes ophalen
    Route::get('verlof/types', fn() =>
        Verloftype::orderBy('naam')->get(['verlof_type_id', 'naam', 'betaald'])
    );

    // Verlofsaldo ophalen
    Route::get('verlof/saldo', function (Request $request) {
        return response()->json([
            'verlofsaldo' => $request->user()->verlofsaldo,
        ]);
    });

    // Verlofaanvragen
    Route::post('verlof/aanvragen', [VerlofaanvraagController::class, 'store']);
    Route::get('verlof/mijn-aanvragen', [VerlofBeoordelingController::class, 'mijnAanvragen']);

    // Beoordeling (manager)
    Route::get('verlof/beoordeling', [VerlofBeoordelingController::class, 'index']);
    Route::post('verlof/beoordeling/{aanvraag}/accept', [VerlofBeoordelingController::class, 'accept']);
    Route::post('verlof/beoordeling/{aanvraag}/reject', [VerlofBeoordelingController::class, 'reject']);

    // Gebruikersbeheer
    Route::get('user', [AuthController::class, 'user']);
    Route::put('user', [AuthController::class, 'updateUser']);
    Route::put('user/password', [AuthController::class, 'updatePassword']);
});
