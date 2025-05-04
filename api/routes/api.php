<?php

use App\Http\Controllers\AirlineController;
use App\Http\Controllers\AirplaneController;
use App\Http\Controllers\AirportController;
use App\Http\Controllers\FlightController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\RouteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::middleware('authorized:airlines')->group(function () {
        Route::apiResource('airlines', AirlineController::class);
    });

    Route::middleware('authorized:airplanes')->group(function () {
        Route::apiResource('airplanes', AirplaneController::class);
    });

    Route::middleware('authorized:airports')->group(function () {
        Route::apiResource('airports', AirportController::class);
    });

    Route::middleware('authorized:flights')->group(function () {
        Route::apiResource('flights', FlightController::class);
    });

    Route::middleware('authorized:routes')->group(function () {
        Route::apiResource('routes', RouteController::class);
    });

    Route::middleware('authorized:inquiries')->group(function () {
        Route::apiResource('inquiries', InquiryController::class);
    });

    Route::middleware('authorized:reservations')->group(function () {
        Route::apiResource('reservations', ReservationController::class);
    });
});
