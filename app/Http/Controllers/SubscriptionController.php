<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Order;
use App\Models\Subscription;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Redirect;

class SubscriptionController extends Controller
{
    public function index()
    {

        // dd(Order::with('user')->get());
        return Inertia::render('Subscriptions/Index', [
            'filters' => Request::all('search', 'trashed'),
            'subscriptions' => Subscription::with('user','plan')
                ->paginate(10)
                ->withQueryString()
                ->through(fn ($subscription) => [
                    'id' => $subscription->id,
                    'subs_id' => $subscription->subs_id,
                    'user' => $subscription->user ? $subscription->user->only('name') : null,
                    'plan' => $subscription->plan ? $subscription->plan : null,
                ]),
        ]);
    }

    public function create()
    {
        // 
    }

    public function store()
    {
        // 
    }

    public function edit(Request $request, $id)
    {
        $order= Order::find($id);
        if ($order->order_status) {
            if ($order->order_status->status == 'PENDING') {
                $o_status = 'ACCEPTED';
                $status_color = 'green';
            } else {
                $o_status = $order->order_status->status;
                $status_color = 'yellow';
            }
            
        } else {
            $o_status = 'PENDING';
            $status_color = 'red';
        }

        if ($order->order_status && $order->order_status->worker) {
            $worker = $order->order_status->worker()->select(
                'id',
                'first_name',
                'last_name',
                'phone',
                'email',
                'email_verified_at',
                'photo_path'
            )->first();
        } else {
            $worker = null;
        }
        return Inertia::render('Orders/View', [
            'order' => [
                'id' => $order->id,
                'uuid' => $order->uuid,
                'user' => $order->user ? $order->user->only('name') : null,
                'city' => $order->city,
                'state' => $order->phone,
                'country' => $order->city,
                'lat' => $order->lat,
                'lng' => $order->lng,
                'full_address' => $order->full_address,
                'area'=>$order->order_area ? $order->order_area : null,
                'worker'=>  $worker,
                'status' => $o_status,
                'status_color' => $status_color,
            ],
        ]);
    }

    public function update()
    {
        //
    }

    public function destroy()
    {
        //
    }

    public function restore()
    {
        //
    }
}
