<?php

use App\Http\Controllers\Api\AfdelingVerlofOverzichtController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\VerlofaanvraagController;
use App\Http\Controllers\VerlofBeoordelingController;
use App\Http\Controllers\Admin\LoginAttemptAdminController;
use App\Http\Controllers\Admin\UserRoleController;
use App\Http\Controllers\Api\BezettingController;
use App\Models\Verloftype;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'apiLogin']);

// **Authenticated routes**
Route::middleware('auth:sanctum')->group(function () {

    // Auth user
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Afdelingsverlof overzicht
    Route::get('/verlof/afdeling/overzicht', [AfdelingVerlofOverzichtController::class, 'index']);

    // Verlofaanvragen
    Route::get('verlof/mijn-aanvragen', [VerlofBeoordelingController::class, 'mijnAanvragen']);
    Route::post('verlof/aanvragen', [VerlofaanvraagController::class, 'store']);

    // Verlof beoordeling
    Route::get('verlof/beoordeling', [VerlofBeoordelingController::class, 'index']);
    Route::post('verlof/beoordeling/{aanvraag}/accept', [VerlofBeoordelingController::class, 'accept']);
    Route::post('verlof/beoordeling/{aanvraag}/reject', [VerlofBeoordelingController::class, 'reject']);
    Route::post('verlof/bulk-accept', [VerlofBeoordelingController::class, 'bulkAccept']);
    Route::post('verlof/bulk-reject', [VerlofBeoordelingController::class, 'bulkReject']);

    // Bezetting
    Route::get('verlof/bezetting', [BezettingController::class, 'index']);
    Route::get('verlof/bezetting/dag', [BezettingController::class, 'day']);

    // Admin: gebruikers
    Route::get('/admin/users', [UserRoleController::class, 'index']);
    Route::put('/admin/users/{id}/role-afdeling', [UserRoleController::class, 'updateRoleAfdeling']);

    // Admin: login attempts
    Route::get('admin/login-attempts', [LoginAttemptAdminController::class, 'index']);

    // User profiel
    Route::put('user', [AuthController::class, 'updateUser']);
    Route::put('user/password', [AuthController::class, 'updatePassword']);

    // Verloftypes
    Route::get('verlof/types', fn() => Verloftype::orderBy('naam')->get(['verlof_type_id', 'naam', 'betaald']));

    // Verlofsaldo
    Route::get('verlof/saldo', function (Request $request) {
        return response()->json(['verlofsaldo' => $request->user()->verlofsaldo]);
    });
});
