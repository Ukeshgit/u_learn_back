
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;

Route::middleware('api')->group(function () {
    Route::post('/auth/register', [UserController::class, 'createUser']);
    Route::post('/auth/login', [UserController::class, 'loginUser']);
});