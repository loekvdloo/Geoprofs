<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VerlofaanvraagController;
use App\Http\Controllers\VerlofBeoordelingController;
use App\Models\Verloftype;

// Login via API
Route::post('login', [AuthController::class, 'apiLogin']);

// Alleen beschermd via Bearer-token
Route::middleware('auth:sanctum')->group(function () {

    // Logout en user info
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthController::class, 'user']);

    // Verlof types ophalen
    Route::get(
        'verlof/types',
        fn() =>
        Verloftype::orderBy('naam')->get(['verlof_type_id', 'naam', 'betaald'])
    );

    // Verlof aanvragen indienen
    Route::post('verlof/aanvragen', [VerlofaanvraagController::class, 'store']);

    // Verlof aanvragen ophalen voor beoordeling
    Route::get('verlof/beoordeling', [VerlofBeoordelingController::class, 'index']);

    // Verlof aanvragen beoordelen
    Route::post('verlof/beoordeling/{aanvraag}/accept', [VerlofBeoordelingController::class, 'accept']);
    Route::post('verlof/beoordeling/{aanvraag}/reject', [VerlofBeoordelingController::class, 'reject']);
});
