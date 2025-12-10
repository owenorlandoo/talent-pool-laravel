<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', [DashboardController::class, 'index']);
Route::get('/compare', [DashboardController::class, 'compare']);

// RUTE BARU UNTUK UPLOAD
Route::post('/import', [DashboardController::class, 'importData']);
