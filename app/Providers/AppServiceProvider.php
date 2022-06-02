<?php

namespace App\Providers;

use App\Sockets\WebSocketHandler;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use BeyondCode\LaravelWebSockets\Facades\WebSocketsRouter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Model::unguard();

        // WebSocketsRouter::webSocket('/test_socket', WebSocketHandler::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
