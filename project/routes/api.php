<?php

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', 'User\LoginController@login');

Route::get('/category/{slug}','Front\FrontendController@category');
Route::get('/details/{slug}','Front\FrontendController@details');

Route::group(['middleware' => ['auth:sanctum'],'prefix'=>'user'], function() {

        Route::post('/bid/store','User\BidController@store');
        Route::post('/logout', 'User\LoginController@logout');
});

