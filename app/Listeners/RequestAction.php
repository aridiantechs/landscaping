<?php

namespace App\Listeners;

use App\Models\User;
use App\Events\RequestRecieved;

class RequestAction
{
    
    public function __construct()
    {
        //
    }
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(/* RequestRecieved $event */)
    {
        // $data = $event->message;
        // store dummy user
        // $user = User::create([
        //     'name' => 'ghafwghdfhgawfd',
        //     'email' => 'testevent@test.com',
        //     'password' => bcrypt('12341234'),
        // ]);

        dd(983475834785439587349548);
    }
}