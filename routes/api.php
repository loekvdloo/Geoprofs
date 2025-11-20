<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\VerlofaanvraagController;
use App\Http\Controllers\VerlofBeoordelingController;
use App\Http\Controllers\Admin\LoginAttemptAdminController;
use App\Models\Verloftype;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'apiLogin']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('verlof/types', fn() => Verloftype::orderBy('naam')->get(['verlof_type_id', 'naam', 'betaald']));
    Route::post('verlof/aanvragen', [VerlofaanvraagController::class, 'store']);
    Route::get('verlof/mijn-aanvragen', [VerlofBeoordelingController::class, 'mijnAanvragen']);
    Route::get('verlof/beoordeling', [VerlofBeoordelingController::class, 'index']);
    Route::post('verlof/beoordeling/{aanvraag}/accept', [VerlofBeoordelingController::class, 'accept']);
    Route::post('verlof/beoordeling/{aanvraag}/reject', [VerlofBeoordelingController::class, 'reject']);
    Route::put('user', [AuthController::class, 'updateUser']);
    Route::put('user/password', [AuthController::class, 'updatePassword']);
    Route::get('verlof/saldo', function (Request $request) {
        return response()->json([
            'verlofsaldo' => $request->user()->verlofsaldo,
        ]);
    });

    Route::get('admin/login-attempts', [LoginAttemptAdminController::class, 'index']);
});
