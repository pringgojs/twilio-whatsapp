<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TwilioController;

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
Route::prefix('v1')->group(function () {
    Route::post('webhook', [TwilioController::class, 'webhook']);
    Route::post('send-message-media', [TwilioController::class, 'sendMessageMedia']);
    Route::post('send-message-text', [TwilioController::class, 'sendMessageText']);
    Route::get('test-message', [TwilioController::class, 'test']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
