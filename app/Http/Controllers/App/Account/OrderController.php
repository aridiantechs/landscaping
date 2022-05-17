<?php

namespace App\Http\Controllers\App\Account;

use App\Models\Order;
use App\Models\OrderStatus;
use Illuminate\Http\Request;
use App\Http\Requests\OrderRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Http\Resources\OrderResourceCollection;
use App\Traits\ApiResponser;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $orders = OrderStatus::with('order')->get()->pluck('order');
        
        return $this->sendResponse(new OrderResourceCollection($orders), 'Orders Listing.');
    }

    public function worker_action(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'order_id'=>'required|exists:orders,uuid',
            'status' => 'required|in:ACCEPTED,REJECTED,SCHEDULE',
        ]);

        if ($validator->fails()) {
            $fillable = new Order;
            $fillable = $fillable->getFillable();
            $valid_errors = $this->formatErrors($fillable,$validator->errors());
            return $this->validationError('Validation Error.',$valid_errors);
        }

        $order = Order::where('uuid', $uuid)->first();
        $order->order_responses()->create([
            'worker_id' => auth()->user()->id,
            'status' => $request->status,
        ]);

        return $this->sendResponse(new OrderResource($order), 'Order Status Updated.');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    // public function store(Request $request)
    // {
    //     //
    // }
    public function store(OrderRequest $request)
    {
        $req=Order::create([
            'uuid' => unique_serial('orders','uuid',null),
            'user_id'=>auth()->user()->id,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'lat'   => $request->lat,
            'lng'   => $request->lng,
            'full_address' => $request->full_address,
        ]);
        

        // OrderItem::insert([
        //     'order_id' => $req->id,
        //     'company_id' => $request->company_id,
        //     'service_id' => $request->service_id,
        //     'se_id' => $request->se_id,
        // ]);

        //store order status

        // Send Push notification
        // $this->sendNotification($req);

        return $this->sendResponse(new OrderResource($req), 'Order Created.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function show(Order $order, Request $request)
    {
        //
    }
    

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function edit(Order $order)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Order $order)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        //
    }
}
