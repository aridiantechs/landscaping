<?php

use App\Events\RequestRecieved;
use Ratchet\ConnectionInterface;
use App\Sockets\WebSocketHandler;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\ImagesController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\ContactsController;
use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrganizationsController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\OrdersController;
use BeyondCode\LaravelWebSockets\Facades\WebSocketsRouter;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

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

//test notify
Route::get('/test_notify', function () {

    // $connection = new ConnectionInterface;
    // $socketId =  '631178440.316680364';
    // $connection->socketId = $socketId;
    // $connection->app = new \stdClass();
    // $connection->app->id = 'my_app';

    // $connection->send('asdasd');


    

    // $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    // $conexion = socket_connect($socket , '127.0.0.1', 6001);
    // $msg = "Ping !";
    // $len = strlen($msg);

    // socket_sendto($socket,$msg, $len, 0, 'test_socket', 1223);

    // socket_close($socket);
    // dd($conexion);
    // try {
        broadcast(new RequestRecieved(/* auth()->user(), 'You have a new request' */))->toOthers();
    // } catch (\Throwable $th) {
    //    dd($th->getMessage()) ;
    
    // }

    return ['status' => 'Message Sent!'];
        
});

Route::get('terms_and_conditions', function () {
   // open pdf file from public
    return response()->file(public_path('storage/Terms_and_Conditions.pdf'));
});

Route::get('privacy_policy', function () {
    return response()->file(public_path('storage/Privacy_Policy.pdf'));
})->name('contact-us');

Route::get('/contact-us', function () {
    return view('contact-us');
})->name('contact-us');

Route::post('/contact-us', [ContactUsController::class, 'store'])->name('contact-us.store');

// Auth

Route::get('login', [AuthenticatedSessionController::class, 'create'])
    ->name('login')
    ->middleware('guest');

Route::post('login', [AuthenticatedSessionController::class, 'store'])
    ->name('login.store')
    ->middleware('guest');

Route::delete('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->name('logout');

// Dashboard
Route::get('/', [DashboardController::class, 'index'])
    ->name('dashboard')
    ->middleware('auth');

// Users
Route::get('users', [UsersController::class, 'index'])
    ->name('users')
    ->middleware('auth');

Route::get('users/create', [UsersController::class, 'create'])
    ->name('users.create')
    ->middleware('auth');

Route::post('users', [UsersController::class, 'store'])
    ->name('users.store')
    ->middleware('auth');

Route::get('users/{user}/edit', [UsersController::class, 'edit'])
    ->name('users.edit')
    ->middleware('auth');

Route::put('users/{user}', [UsersController::class, 'update'])
    ->name('users.update')
    ->middleware('auth');

Route::delete('users/{user}', [UsersController::class, 'destroy'])
    ->name('users.destroy')
    ->middleware('auth');

Route::put('users/{user}/restore', [UsersController::class, 'restore'])
    ->name('users.restore')
    ->middleware('auth');

// Organizations

Route::get('organizations', [OrganizationsController::class, 'index'])
    ->name('organizations')
    ->middleware('auth');

Route::get('organizations/create', [OrganizationsController::class, 'create'])
    ->name('organizations.create')
    ->middleware('auth');

Route::post('organizations', [OrganizationsController::class, 'store'])
    ->name('organizations.store')
    ->middleware('auth');

Route::get('organizations/{organization}/edit', [OrganizationsController::class, 'edit'])
    ->name('organizations.edit')
    ->middleware('auth');

Route::put('organizations/{organization}', [OrganizationsController::class, 'update'])
    ->name('organizations.update')
    ->middleware('auth');

Route::delete('organizations/{organization}', [OrganizationsController::class, 'destroy'])
    ->name('organizations.destroy')
    ->middleware('auth');

Route::put('organizations/{organization}/restore', [OrganizationsController::class, 'restore'])
    ->name('organizations.restore')
    ->middleware('auth');

// Contacts

Route::get('contacts', [ContactsController::class, 'index'])
    ->name('contacts')
    ->middleware('auth');

Route::get('contacts/create', [ContactsController::class, 'create'])
    ->name('contacts.create')
    ->middleware('auth');

Route::post('contacts', [ContactsController::class, 'store'])
    ->name('contacts.store')
    ->middleware('auth');

Route::get('contacts/{contact}/edit', [ContactsController::class, 'edit'])
    ->name('contacts.edit')
    ->middleware('auth');

Route::put('contacts/{contact}', [ContactsController::class, 'update'])
    ->name('contacts.update')
    ->middleware('auth');

Route::delete('contacts/{contact}', [ContactsController::class, 'destroy'])
    ->name('contacts.destroy')
    ->middleware('auth');

Route::put('contacts/{contact}/restore', [ContactsController::class, 'restore'])
    ->name('contacts.restore')
    ->middleware('auth');

// Contact Us

Route::get('contact_us', [ContactUsController::class, 'index'])
    ->name('contact_us')
    ->middleware('auth');

// Reports

Route::get('reports', [ReportsController::class, 'index'])
    ->name('reports')
    ->middleware('auth');

// Settings

Route::get('settings', [SettingsController::class, 'edit'])
    ->name('settings')
    ->middleware('auth');

Route::put('settings/{setting}', [SettingsController::class, 'update'])
    ->name('settings.update')
    ->middleware('auth');

// Orders

Route::get('orders', [OrdersController::class, 'index'])
    ->name('orders')
    ->middleware('auth');


// Images

Route::get('/img/{path}', [ImagesController::class, 'show'])
    ->where('path', '.*')
    ->name('image');