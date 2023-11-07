<?php

use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\SpaceController;
use Illuminate\Support\Facades\Route;


Route::get('/categories', [CategoryController::class, 'index']);
Route::post('/categories', [CategoryController::class, 'store']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);
Route::put('/categories/{category}', [CategoryController::class, 'update']);
Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

Route::get('/spaces', [SpaceController::class, 'index']);
Route::post('/spaces', [SpaceController::class, 'store']);
Route::get('/spaces/{space}', [SpaceController::class, 'show']);
Route::put('/spaces/{space}', [SpaceController::class, 'update']);
Route::delete('/spaces/{space}', [SpaceController::class, 'destroy']);

Route::get('/plans', [PlanController::class, 'index']);
Route::post('/plans', [PlanController::class, 'store']);
Route::get('/plans/{plan}', [PlanController::class, 'show']);
Route::put('/plans/{plan}', [PlanController::class, 'update']);
Route::delete('/plans/{plan}', [PlanController::class, 'destroy']);

Route::get('/booking', [BookingController::class, 'index']);
Route::post('/booking', [BookingController::class, 'store']);
Route::get('/booking/{booking}', [BookingController::class, 'show']);
Route::put('/booking/{booking}', [BookingController::class, 'update']);
Route::delete('/booking/{booking}', [BookingController::class, 'destroy']);

//Payment
Route::post('/payment{bookingId}', [BookingController::class, 'makePayment'])->name('payment');
Route::get('/payment/callback', [BookingController::class, 'paymentCallback'])->name('payment.callback'); //callbackurl
