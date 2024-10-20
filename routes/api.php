<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-totp', [AuthController::class, 'verifyTOTP']);


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
