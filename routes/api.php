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

/* Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
}); */

Route::group([
    'namespace' => '\App\Http\Controllers\App',
    'prefix' => 'auth'

], function ($router) {

    Route::post('login', 'AuthController@login');
    Route::post('forgot/password', 'AuthController@sendResetLinkEmail');
    Route::post('reset/password', 'AuthController@reset_password');
    Route::post('update/password', 'AuthController@change_password');

    Route::group(['middleware' => ['auth:sanctum']], function () {
        Route::post('logout', 'AuthController@logout');
        Route::post('update/password', 'AuthController@change_password');
        Route::get('me', 'AuthController@me')/* ->middleware('otp_verified') */;
       
        Route::get('refresh', 'AuthController@refreshToken');
        Route::post('otp', 'AuthController@verifyEmailUsingOtp');

    });
    
    // Route::post('refresh', 'AuthController@refresh');
    Route::post('signup', 'AuthController@signup');
});

Route::group([
    'namespace' => '\App\Http\Controllers\App',
    'prefix' => 'account',
    'middleware'=>['auth:sanctum', 'isVerifiedUser'/* ,'otp_verified' */]

], function ($router) {

    Route::post('profile', 'Account\ProfileController@update');
    Route::get('profile', 'Account\ProfileController@index');

});
