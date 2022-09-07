<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use App\Mail\SubscriptionFailMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use App\Mail\SubscriptionExpireSoonMail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Http\Controllers\App\Account\SubscriptionController;

class SubscriptionRenew implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $workers= User::has('subscriptions')->get();

        foreach ($workers as $worker) {
            
            if ($worker && $worker->dayOldSubscription()) {
                $subscription = $worker->dayOldSubscription();

                // send subscription fail email
                $data = [
                    'user' => $worker,
                    'subscription' => $subscription,
                    'plan' => $subscription->plan,
                    'error' => $res->errors,
                ];

                $email = new SubscriptionFailMail($data);
                Mail::to($worker->email )->send($email);
            }
            
        }
    }
}
