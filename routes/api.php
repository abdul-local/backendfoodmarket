<?php

use App\Http\Controllers\API\Foodcontroller;
use App\Http\Controllers\API\MidtransController;
use App\Http\Controllers\API\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->group( function () {
    Route::get('user', [UserController::class,  'fetch']);
    Route::post('user', [UserController::class,  'updateProfile']);
    Route::post('user/photo', [UserController::class,  'updatePhoto']);
    Route::post('logout', [UserController::class,  'logout']);
    Route::get('transaction',[TransactionController::class, 'all']);
    Route::get('transaction/{id}',[TransactionController::class, 'update']);
    Route::get('checkout',[TransactionController::class, 'checkout']);

    
});
Route::post('login', [UserController::class,  'login']);
Route::post('register', [UserController::class,  'register']);
Route::get('food',[Foodcontroller::class, 'all']);
Route::post('midtrans/callback',[MidtransController::class,'callback']);
