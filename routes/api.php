<?php

use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/register', [App\Http\Controllers\Api\AuthController::class, 'register']);
Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login']);

Route::middleware('auth:sactum')->group(function(){
    Route::get('/user',function(Request $request){
        return $request->user();
    });

    Route::apiResource('tasks',TaskController::class);
});