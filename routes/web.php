<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\EventController;

Route::get('/events/search', [EventController::class, 'search']);
Route::get('/events', [EventController::class, 'index']);
Route::get('/events/naive', [EventController::class, 'naive']);
Route::get('/', function () {
    return view('welcome');
});

Route::get('/report', [ReportController::class, 'index']);