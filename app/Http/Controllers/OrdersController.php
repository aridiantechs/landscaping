<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class OrdersController extends Controller
{
    public function index()
    {

        // dd(Order::with('user')->get());
        return Inertia::render('Orders/Index', [
            'filters' => Request::all('search', 'trashed'),
            'orders' => Order::with('user')
                ->orderByName()
                ->filter(Request::only('search', 'trashed'))
                ->paginate(10)
                ->withQueryString()
                ->through(fn ($order) => [
                    'id' => $order->id,
                    'user' => $order->user ? $order->user->only('name') : null,
                    'city' => $order->city,
                    'state' => $order->phone,
                    'country' => $order->city,
                    'lat' => $order->lat,
                    'lng' => $order->lng,
                    'full_address' => $order->full_address,
                    // 'organization' => $order->organization ? $order->organization->only('name') : null,
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

    public function edit()
    {
        // 
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
