<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SpaController;

// Rota de login nomeada para evitar erro RouteNotFound
Route::get('/login', [SpaController::class, 'login'])->name('login');

// Serve React app for all non-API, non-admin routes
Route::get('/{any}', [SpaController::class, 'index'])->where('any', '(?!api|admin).*');
