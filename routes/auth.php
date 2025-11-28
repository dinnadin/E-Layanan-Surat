<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// === AUTH ===
Route::get('/login', [AuthController::class, 'loginForm'])->name('login.form');
Route::get('/', [AuthController::class, 'loginForm'])->name('login.form');
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::get('/register', [AuthController::class, 'registerForm'])->name('register.form');
Route::post('/register', [AuthController::class, 'register'])->name('register');

Route::get('/logout', [AuthController::class, 'logout'])->name('logout');