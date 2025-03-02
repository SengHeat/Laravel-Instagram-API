<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('register', [\App\Http\Controllers\AuthController::class, 'register']);
Route::post('login', [\App\Http\Controllers\AuthController::class, 'login']);
Route::middleware('auth:api')->post('user', [AuthController::class, 'getUser']);
Route::middleware('auth:api')->post('logout', [AuthController::class, 'logout']);
Route::middleware('auth:api')->delete('delete', [AuthController::class, 'deleteUser']);

