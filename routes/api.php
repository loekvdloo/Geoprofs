<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VerlofaanvraagController;
use App\Models\Verloftype;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'apiLogin']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);


// Protected API routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthController::class, 'user']);
    Route::get('verlof/types', function () {
        return Verloftype::orderBy('naam')->get(['verlof_type_id', 'naam', 'betaald']);
    });
    Route::post('verlof/aanvragen', [VerlofaanvraagController::class, 'store']);
});
