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
    'prefix' => '1.0/auth'

], function ($router) {

    Route::post('login', 'AuthController@login');
    Route::post('forgot/password', 'AuthController@sendResetLinkEmail');
    Route::post('reset/password', 'AuthController@reset_password');

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
    'namespace'   => '\App\Http\Controllers\App',
    'prefix'      => '1.0/worker',
    'middleware'  => ['auth:sanctum', 'isVerifiedUser'/* ,'otp_verified' */]
], function ($router) {

    // Worker Profile
    Route::get('profile', 'Account\ProfileController@index');
    Route::post('profile', 'Account\ProfileController@update');
    Route::post('profile/password/update', 'Account\ProfileController@update_password');
    // Worker Profile Ends here

    // Worker Side Notifications
    Route::get('notification/listing/get', 'Account\NotificationController@worker_index'); /* Notification Listing Type */
    // Worker Side Notifications Ends Here
    
    // Worker Side Orders
    Route::get('order/listing', 'Account\OrderController@index'); /* Order Listing */
    Route::post('order/action', 'Account\OrderController@worker_action'); /* Order Action */
    Route::post('order/quote/submit', 'Account\OrderController@worker_quoteSubmit'); /* Order Quote Submit */
    Route::get('order/quote/get', 'Account\OrderController@worker_quoteGet'); /* Order Quote Get */    
    
    Route::post('order/{order}/schedule', 'Account\OrderController@schedule'); /* Order Action */

    // Route::post('order/quote/action', 'Account\OrderController@worker_quoteAction'); /* Order Quote Action Will only be performed by Customer */
    Route::get('order/detail', 'Account\OrderController@worker_detail'); /* Order Details */
    // Worker Side Orders Ends Here

});

Route::group([
    'namespace'   => '\App\Http\Controllers\App',
    'prefix'      => '1.0/customer',
    'middleware'  => ['auth:sanctum', 'isVerifiedUser'/* ,'otp_verified' */]
], function ($router) {

    // Customer Profile
    Route::get('profile', 'Account\ProfileController@index');
    Route::post('profile', 'Account\ProfileController@update');
    Route::post('profile/password/update', 'Account\ProfileController@update_password');
    // Customer Profile Ends here

    // Customer Side Notifications
    Route::get('notification/listing/get', 'Account\NotificationController@customer_index'); /* Notification Listing Type */
    // Customer Side Notifications Ends Here

    // Customer Side Orders
    Route::get('order/listing/get', 'Account\OrderController@customer_index'); /* Order Listing */
    Route::post('order/action', 'Account\OrderController@customer_action'); /* Order Action */
    Route::post('order/quote/submit', 'Account\OrderController@customer_quoteSubmit'); /* Order Quote Submit */
    Route::get('order/quote/get', 'Account\OrderController@customer_quoteGet'); /* Order Quote Get */    
    Route::post('order/quote/action', 'Account\OrderController@customer_quoteAction'); /* Order Quote Action Will only be performed by Customer */
    Route::get('order/details', 'Account\OrderController@customer_details'); /* Order Details */
    // Customer Side Orders Ends Here
});
