<?php

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

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/logout', 'App\Http\Controllers\Whatsapp\AuthController@logout');
});
Route::post('/register', 'App\Http\Controllers\Whatsapp\AuthController@register');
Route::post('/login', 'App\Http\Controllers\Whatsapp\AuthController@login');
