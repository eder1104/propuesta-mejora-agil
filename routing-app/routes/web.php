<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeliveryRouteController;

Route::get('/', [DeliveryRouteController::class, 'index']);
