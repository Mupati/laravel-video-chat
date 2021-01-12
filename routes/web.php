<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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


Route::get('/video-chat', function () {
    // fetch all users apart from the authenticated user
    $users = User::where('id', '<>', Auth::id())->get();
    return view('video-chat', ['users' => $users]);
});

// Endpoints to alert call or receive call.
Route::post('/video/call-user', 'App\Http\Controllers\VideoChatController@callUser');
Route::post('/video/accept-call', 'App\Http\Controllers\VideoChatController@acceptCall');

// Agora Video Call Endpoints
Route::get('/agora-chat', function () {
    // fetch all users apart from the authenticated user
    $users = User::where('id', '<>', Auth::id())->get();
    return view('agora-chat', ['users' => $users]);
});


// Auth::routes();
Auth::routes(['register' => false]);
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
