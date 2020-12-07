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

Route::prefix('auth')->group(function(){
    //api/auth/register
    Route::post('/register', 'AuthController@register');
    Route::post('/login', 'AuthController@login');
    Route::post('/sendResetPasswordPin', 'AuthController@sendResetPasswordPin');
    Route::post('/doVerify', 'AuthController@doVerify');
    Route::get('/getVerify', 'AuthController@getVerify');
    Route::get('/logout', 'AuthController@logout')->middleware('auth:api');
    Route::get('/user', 'AuthController@user')->middleware('auth:api');
    Route::get('authentication-failed', 'AuthController@authFailed')->name('auth-failed');

});
