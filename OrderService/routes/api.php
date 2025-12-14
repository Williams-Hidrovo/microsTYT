<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\OrderController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('jwt.auth')->group(function () {
    Route::apiResource('clientes', CustomerController::class);
    Route::apiResource('pedidos', OrderController::class);
    Route::patch('pedidos/{order}/complete', [OrderController::class, 'complete']);
    Route::patch('pedidos/{order}/cancel', [OrderController::class, 'cancel']);
});
