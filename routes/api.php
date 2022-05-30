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
        Route::get('me', 'AuthController@me')/* ->middleware('otp_verified') */;

        Route::get('refresh', 'AuthController@refreshToken');
        Route::post('otp', 'AuthController@verifyEmailUsingOtp');

        Route::get('profile', 'Account\ProfileController@index');
        Route::post('profile', 'Account\ProfileController@update');
        Route::post('profile/password/update', 'Account\ProfileController@update_password');
    });

    // Route::post('refresh', 'AuthController@refresh');
    Route::post('signup', 'AuthController@signup');
});

Route::group([
    'namespace'   => '\App\Http\Controllers\App',
    'prefix'      => '1.0/worker',
    'middleware'  => ['auth:sanctum', 'IsVerifiedUser', 'IsWorker'/* ,'otp_verified' */]
], function ($router) {

    // Worker Side Notifications
    Route::get('notification/listing/get', 'Account\NotificationController@worker_index'); /* Notification Listing Type */
    // Worker Side Notifications Ends Here

    // Worker Side Orders
    Route::get('order/listing', 'Account\OrderController@index');         /* Order Listing */
    Route::post('order/action', 'Account\OrderController@worker_action'); /* Order Action */
    Route::post('order/{order}/schedule', 'Account\OrderController@schedule');     /* Order Action */
    Route::post('order/{order}/quote/submit', 'Account\OrderController@quoteSubmit');   /* Order Quote Submit */
    Route::get('order/{order}/detail', 'Account\OrderController@show');          /* Order Details */
    // Route::get('order/{order}/quote', 'Account\OrderController@worker_quoteGet'); 
    // Route::post('order/quote/action', 'Account\OrderController@worker_quoteAction'); /* Order Quote Action Will only be performed by Customer */
    // Worker Side Orders Ends Here

});

Route::group([
    'namespace'   => '\App\Http\Controllers\App',
    'prefix'      => '1.0/customer',
    'middleware'  => ['auth:sanctum', 'IsVerifiedUser', 'IsEndUser'/* ,'otp_verified' */]
], function ($router) {

    // Customer Side Notifications
    Route::get('notification/listing/get', 'Account\NotificationController@customer_index'); /* Notification Listing Type */
    // Customer Side Notifications Ends Here

    // Customer Side Orders
    Route::get('order/listing', 'Account\OrderController@index'); /* Order Listing */
    Route::post('order/post', 'Account\OrderController@store'); /* Order Listing */
    Route::post('order/action', 'Account\OrderController@customer_action'); /* Order Action */
    Route::post('order/{order}/quote/action',  'Account\OrderController@customer_quoteAction'); /* Order Quote Action Will only be performed by Customer */
    Route::get('order/{order}/detail', 'Account\OrderController@show');
    // Route::get ('order/quote/get',     'Account\OrderController@customer_quoteGet');
    // Customer Side Orders Ends Here
});

Route::get('/test_fcm', function (Request $request) {

    fcm()
    ->to($request->query('token'))
    ->priority('high')
    ->timeToLive(0)
    ->notification([
        'title' => $request->query('title') ?? 'Test FCM',
        'body' => $request->query('body') ?? 'This is a test of FCM',
    ])
    ->send();

    dd($request->query('token'),config('laravel-fcm.server_key'));
});

Route::get('/test_fcm_data', function (Request $request) {

    fcm()
    ->to($request->query('token'))
    ->priority('high')
    ->timeToLive(0)
    // ->notification([
    //     'title' => 'Test FCM',
    //     'body' => 'This is a test of FCM',
    // ])
    ->data([
        'title' => $request->query('title') ?? 'Test FCM',
        'body' => $request->query('body') ?? 'This is a test of FCM',
    ])
    ->send();

    dd($request->query('token'),config('laravel-fcm.server_key'));
});