<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Simple comments viewer/poster for demo purposes
Route::get('/comments-demo', function () {
    return view('comments-demo');
});

// Dashboard to trigger API calls and show results
Route::get('/comments-dashboard', function () {
    return view('comments-dashboard');
});
