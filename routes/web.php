<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::post('/broadcasting/auth', function (Illuminate\Http\Request $req) {
    $socketId = $req->socket_id;
    $channelName = $req->channel_name;

    // App Key dan Secret dari config atau .env file
    $key = config('broadcasting.connections.pusher.key');
    $secret = config('broadcasting.connections.pusher.secret');

    // Buat signature dengan HMAC SHA256
    $stringToSign = $socketId . ':' . $channelName;
    $signature = hash_hmac('sha256', $stringToSign, $secret);

    return response()->json([
        'auth' => "$key:$signature"
    ]);
    });


// require __DIR__.'/auth.php';
