<?php

use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\HistoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AnalysisController::class, 'uploadFiles']);

Route::post('/report', [AnalysisController::class, 'reportFiles']);

Route::get('/history', [HistoryController::class, 'history']);
