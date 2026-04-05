<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeliveryRouteController;

Route::get('/', [App\Http\Controllers\DeliveryRouteController::class, 'index']);
Route::get('/api/optimize', [App\Http\Controllers\DeliveryRouteController::class, 'optimize']);
