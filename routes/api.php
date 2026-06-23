<?php

use App\Http\Controllers\Api\AdminProfileController;
use App\Http\Controllers\Api\MedicalStatusController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('external.api:external-admin:read')->group(function () {
    Route::get('/external/admins', [AdminProfileController::class, 'index']);
    Route::get('/external/admins/options', [AdminProfileController::class, 'options']);
    Route::get('/external/admins/{admin_id}', [AdminProfileController::class, 'externalShow']);
    Route::get('/external/admin-profile', [AdminProfileController::class, 'lookup']);
});

Route::middleware('external.api:external-admin:update')->group(function () {
    Route::put('/external/admins/{admin_id}', [AdminProfileController::class, 'externalUpdate']);
});

Route::middleware('external.api:medical-status:read')->group(function () {
    Route::get('/external/students/{student_id}/medical-status', [MedicalStatusController::class, 'show']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/admin/profile/{admin_id}', [AdminProfileController::class, 'show']);
    Route::post('/admin/profile/update', [AdminProfileController::class, 'update']);
});
