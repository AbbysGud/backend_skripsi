<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\SensorDataController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NgrokServerController;
use App\Http\Controllers\HydrationController;
use App\Events\HelloEvent;
use App\Events\ModeEvent;
use App\Events\WeightEvent;
use Illuminate\Support\Facades\Log;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//User API
Route::middleware('auth:sanctum')->post('/logout', [UserController::class, 'logout']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/register', [UserController::class, 'register']);
Route::put('/users/{id}/rfid', [UserController::class, 'updateRFID']);
Route::put('/users/{id}/profile', [UserController::class, 'updateProfile']);
Route::get('/users/{id}', [UserController::class, 'getDataUser']);
Route::delete('/users/{id}', [UserController::class, 'deleteUser']);
Route::post('/forgot-password', [UserController::class, 'forgotPassword']);
Route::post('/reset-password', [UserController::class, 'resetPassword']);

//Sensor API
Route::post('/sensor-data', [SensorDataController::class, 'store']);
Route::get('/sensor-data/all', [SensorDataController::class, 'getSensorDataByUserId']);
Route::get('/sensor-data/history', [SensorDataController::class, 'getSensorDataHistoryByUserId']);
Route::get('/sensor-data/date', [SensorDataController::class, 'getByDate']);
Route::get('/sensor-data/date-range', [SensorDataController::class, 'getSensorDataByDate']);

//NGROK API
Route::post('/update-ngrok', [NgrokServerController::class, 'update']);
Route::get('/get-ngrok', [NgrokServerController::class, 'get']);

//WEBSOCKET API
Route::post('/update-hydration', [HydrationController::class, 'updateHydration']);

Route::post("/send-event", function () {
    event(new HelloEvent('Hello from Laravel WebSocket!'));
    return response()->json(['status' => 'Event sent']);
});

Route::post('/send-weight', function (Request $request) {
    $weight = $request->input('weight');
    $message = $request->input('message');
    $mode = $request->input('mode');
    $rfid = $request->input('rfid');
    event(new WeightEvent($weight, $message, $mode, $rfid));
    return response()->json(['status' => 'Data sent']);
});

Route::post('/send-mode', function (Request $request) {
    $message = $request->input('message');
    $user_id = $request->input('user_id');
    event(new ModeEvent($message, $user_id));
    return response()->json(['status' => 'Data sent']);
});

