<?php

use Illuminate\Support\Facades\Route;

// Rota de login nomeada para evitar erro RouteNotFound
Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');

// Serve React app for all non-API, non-admin routes
Route::get('/{any}', function () {
    return file_get_contents(public_path('index.html'));
})->where('any', '(?!api|admin).*');
