<?php

use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\HistoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AnalysisController::class, 'uploadFiles'])->name('home');

Route::post('/report', [AnalysisController::class, 'reportFiles'])->name('home');

Route::get('/history', [HistoryController::class, 'history'])->name('history');
