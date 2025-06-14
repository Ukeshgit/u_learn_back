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