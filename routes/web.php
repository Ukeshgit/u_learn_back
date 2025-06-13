<?php

use Illuminate\Support\Facades\Route;

// Route for the root URL '/'
Route::get('/', function () {
    return view( 'Welcome');
});

// Route for '/about' URL
Route::get('/about', function () {
    return 'This is the About page.';
});
use App\Http\Controllers\Api\UserController;

Route::middleware('api')->group(function () {
    Route::post('/auth/register', [UserController::class, 'createUser']);
    Route::post('/auth/login', [UserController::class, 'loginUser']);
});