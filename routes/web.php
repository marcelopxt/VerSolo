<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\HistoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'create'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth:employee'])->group(function () {
  Route::get('/dashboard', [AnalysisController::class, 'uploadFiles'])->name('dashboard');
  Route::post('/report', [AnalysisController::class, 'reportFiles'])->name('report.submit');
  Route::get('/history', [HistoryController::class, 'history'])->name('history');
});