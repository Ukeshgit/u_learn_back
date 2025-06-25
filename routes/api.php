<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;

Route::post('/login', [UserController::class, 'login']);
// Route::post('/auth/login', [UserController::class, 'loginUser']);