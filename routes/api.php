
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\TaskController;

Route::post('/register', [AuthController::class, 'register'])
    ->middleware('throttle:5,1');

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return response()->json([
            'data' => $request->user(),
        ]);
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    // Admin: persetujuan pendaftaran direktur
    Route::get('/admin/directors/pending', [
        AdminController::class,
        'pendingDirectors',
    ]);

    Route::patch('/admin/directors/{user}/approve', [
        AdminController::class,
        'approveDirector',
    ]);

    Route::patch('/admin/directors/{user}/reject', [
        AdminController::class,
        'rejectDirector',
    ]);

    // CRUD tugas
    Route::apiResource('tasks', TaskController::class);
});
