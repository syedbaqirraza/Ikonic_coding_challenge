<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;

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
Route::get('/users/suggestions', [UserController::class, 'getSuggestions']);
Route::post('/connections/connect', [UserController::class, 'connect']);
Route::get('/connections/sent-requests', [UserController::class, 'getSentRequests']);
Route::delete('/connections/withdraw-request/{connectionId}', [UserController::class, 'withdrawRequest']);
Route::get('/connections/received-requests', [UserController::class, 'getReceivedRequests']);
Route::post('/connections/accept/{connectionId}', [UserController::class, 'acceptRequest']);
Route::get('/connections', [UserController::class, 'getConnections']);
Route::delete('/connections/remove/{connectionId}', [UserController::class, 'removeConnection']);
Route::get('/connections/common/{userId}', [UserController::class, 'getConnectionsInCommon']);
Route::get('/connections/load-more', [UserController::class, 'loadMore']);
Route::get('/connections/count', [UserController::class, 'getCount']);
Route::post('/connections/common/name', [UserController::class, 'getConnectionsNameInCommon']);

