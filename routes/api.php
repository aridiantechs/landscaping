<?php

use Illuminate\Http\Request;
use App\Sockets\WebSocketHandler;
use Illuminate\Support\Facades\Route;
use BeyondCode\LaravelWebSockets\Facades\WebSocketsRouter;

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

        Route::get('resend/otp', 'AuthController@resendOtp');

        Route::get('profile', 'Account\ProfileController@index');
        Route::post('profile', 'Account\ProfileController@update');
        Route::post('profile/password/update', 'Account\ProfileController@update_password');

        Route::get('dashboard', 'Account\DashboardController@index');

        Route::post('remove_account', 'Account\ProfileController@remove_account');
    });

    // Route::post('refresh', 'AuthController@refresh');
    Route::post('signup', 'AuthController@signup');

    Route::post('/social_authenticate', 'AuthController@socialAuthenticate')->name('social_authenticate');
    // renew subscription
    Route::post('renew_subscription', 'Account\SubscriptionController@subscriptionWebhook');// subscription update
});

Route::group([
    'namespace'   => '\App\Http\Controllers\App',
    'prefix'      => '1.0/worker',
    'middleware'  => ['auth:sanctum', 'IsVerifiedUser', 'IsWorker'/* ,'otp_verified' */]
], function ($router) {

    // Worker Side Notifications
    Route::get('notification/listing/get', 'Account\NotificationController@index'); /* Notification Listing Type */
    // Worker Side Notifications Ends Here

    // Worker Side Orders
    Route::get('order/listing', 'Account\OrderController@index');         /* Order Listing */
    Route::post('order/action', 'Account\OrderController@worker_action'); /* Order Action */
    Route::post('order/{order}/schedule', 'Account\OrderController@schedule');     /* Order Action */
    Route::post('order/{order}/quote/submit', 'Account\OrderController@quoteSubmit');   /* Order Quote Submit */
    Route::get('order/{order}/detail', 'Account\OrderController@show')->name('order.show');          /* Order Details */
    Route::get('order/{order}/complete', 'Account\OrderController@complete')->name('order.complete');

    // Route to get update worker location
    Route::get('state/update', 'Account\DashboardController@stateUpdate'); 
    Route::post('location/update', 'Account\DashboardController@locationUpdate');
    
    // Route::get('order/{order}/quote', 'Account\OrderController@worker_quoteGet'); 
    // Route::post('order/quote/action', 'Account\OrderController@worker_quoteAction'); /* Order Quote Action Will only be performed by Customer */
    // Worker Side Orders Ends Here

    // subscriptions
    Route::post('create_card_subscription', 'Account\SubscriptionController@createCardAndSubscription');// subscription on signup
    Route::post('store_subscription', 'Account\SubscriptionController@storeSubscription');// subscription update
    Route::get('cancel_subscription', 'Account\SubscriptionController@cancelSubscription');// subscription update

});

Route::group([
    'namespace'   => '\App\Http\Controllers\App',
    'prefix'      => '1.0/customer',
    'middleware'  => ['auth:sanctum', 'IsVerifiedUser', 'IsEndUser'/* ,'otp_verified' */]
], function ($router) {

    // Customer Side Notifications
    Route::get('notification/listing/get', 'Account\NotificationController@index'); /* Notification Listing Type */
    // Customer Side Notifications Ends Here

    // Customer Side Orders
    Route::get('order/listing', 'Account\OrderController@index'); /* Order Listing */
    Route::post('order/post', 'Account\OrderController@store'); /* Order Listing */
    Route::post('order/action', 'Account\OrderController@customer_action'); /* Order Action */
    Route::post('order/{order}/quote/action',  'Account\OrderController@customer_quoteAction'); /* Order Quote Action Will only be performed by Customer */
    Route::get('order/{order}/detail', 'Account\OrderController@show'); /* Order Details */

    Route::get('get_available_workers', 'Account\DashboardController@getWorkers');
    // Route::get ('order/quote/get',     'Account\OrderController@customer_quoteGet');
    // Customer Side Orders Ends Here
});

Route::get('/test_fcm', function (Request $request) {

    $data = [
        'first_name' => $request->query('first_name') ?? 'Nisar',
        'last_name' => $request->query('last_name') ?? 'Ahmed',
        'about' => $request->query('about') ?? 'about',
        'rating' => $request->query('rating') ?? '5',
    ];
    fcm()
    ->to($request->query('token'))
    ->priority('high')
    ->timeToLive(0)
    ->notification($data)
    ->send();

    return [
        'data' => $data,
        'token' => $request->query('token'),
    ];
});

Route::get('/test_fcm_data', function (Request $request) {
    $data = [
        'first_name' => $request->query('first_name') ?? 'Nisar',
        'last_name' => $request->query('last_name') ?? 'Ahmed',
        'about' => $request->query('about') ?? 'about',
        'rating' => $request->query('rating') ?? '5',
    ];

    try {
        return fcm()
            ->to([
                "e2zg4_g3SP6sdqL6u-x3AF:APA91bHM8SGfdsGsmDinQE2aIPAoVSgo4KVP38nW7f4slywu-OpuhGgQYddNhP0hhsFtU2vupm6MB8nzEHv5MG3vbAV4eo_0GTB-2uyvH1xR6stpYtTFKJqy36ZRhK_3JJMA8eY5x64M",
            ])
            ->priority('high')
            ->timeToLive(0)
            ->data($data)
        ->send();
    } catch (\Exception $e) {
        dd($e->getMessage( ));
    }

    return [
        'data' => $data,
        'token' => $request->query('token') ?? '',
    ];
   
});