<?php

use App\Http\Controllers\Api\AuthController as ApiAuth;
use App\Http\Controllers\VerlofaanvraagController;
use App\Http\Controllers\VerlofBeoordelingController;
use App\Models\Verloftype;
use Illuminate\Support\Facades\Route;

Route::post('/register', [ApiAuth::class, 'register']);
Route::post('/login', [ApiAuth::class, 'apiLogin']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [ApiAuth::class, 'user']);
    Route::post('/logout', [ApiAuth::class, 'logout']);

    Route::get('verlof/types', fn() => Verloftype::orderBy('naam')->get(['verlof_type_id', 'naam', 'betaald']));
    Route::post('verlof/aanvragen', [VerlofaanvraagController::class, 'store']);
    Route::get('verlof/mijn-aanvragen', [VerlofBeoordelingController::class, 'mijnAanvragen']);
    Route::get('verlof/beoordeling', [VerlofBeoordelingController::class, 'index']);
    Route::post('verlof/beoordeling/{aanvraag}/accept', [VerlofBeoordelingController::class, 'accept']);
    Route::post('verlof/beoordeling/{aanvraag}/reject', [VerlofBeoordelingController::class, 'reject']);
    Route::put('user', [ApiAuth::class, 'updateUser']);
    Route::put('user/password', [ApiAuth::class, 'updatePassword']);
});
