<?php

use App\Http\Controllers\ItemController;
use Illuminate\Support\Facades\Route;

Route::post('/items', [ItemController::class, 'store']);
Route::get('/items', [ItemController::class, 'index']);
Route::get('/items/{id}', [ItemController::class, 'show']);
Route::put('/items/{id}', [ItemController::class, 'update']);
Route::patch('/items/{id}', [ItemController::class, 'modify']);
Route::delete('/items/{id}', [ItemController::class, 'destroy']);
