<?php

namespace App\Http\Controllers\App\Account;

use App\Models\Order;
use App\Models\OrderArea;
use App\Models\OrderStatus;
use Illuminate\Http\Request;
use App\Http\Requests\OrderRequest;
use App\Models\OrderResponse;
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
        $orders = Order::all();
        return $this->sendResponse(new OrderResourceCollection($orders), 'Orders Listing.');
    }

    public function worker_action(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,uuid',
            'status' => 'required|in:ACCEPTED,REJECTED,SCHEDULE',
        ]);

        if ($validator->fails()) {
            $fillable = new Order;
            $fillable = $fillable->getFillable();
            $valid_errors = $this->formatErrors($fillable, $validator->errors());
            return $this->validationError('Validation Error.', $valid_errors);
        }

        $order = Order::where('uuid', $request->order_id)->first();
        
        // if order response already exists
        if ( count($order->order_responses) && $order->accepted_response->count()) {
            return $this->validationError('Order already accepted.', []);
        }
        $order_r = new OrderResponse;
        $order_r->order_id = $request->order_id;
        $order_r->user_id = auth()->user()->id;
        $order_r->response_user = auth()->user()->roles()->first()->name ?? '';
        $order_r->response_type = $request->status;
        $order_r->save();

        return $this->sendResponse(new OrderResource($order), 'Order Status Updated.');
    }

    public function schedule(Request $request, $order_id)
    {
        $validator = \Validator::make($request->all(), [
            'time' => 'required',
            'comments' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            $valid_errors = $this->formatErrors(['time', 'comments'], $validator->errors());
            return $this->validationError('Validation Error.', $valid_errors);
        }

        $order_r = OrderResponse::where('order_id', $order_id)->where('user_id', auth()->user()->id)->first();
        if ($order_r->response_type == 'SCHEDULE') {
            $order_r->time = $request->time;
            $order_r->comments = $request->comments;
            $order_r->save();
            return $this->sendResponse(new OrderResource($order_r->order), 'Order Schedule Updated.');
        } else {
            return $this->validationError('Validation Error.', 'Order Status cannot be SCHEDULE');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function quoteSubmit(Request $request, $order_id)
    {
        $validator = \Validator::make($request->all(), [
            'length' => 'required|numeric',
            'width' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            $valid_errors = $this->formatErrors(['length', 'width'], $validator->errors());
            return $this->validationError('Validation Error.', $valid_errors);
        }

        $order = Order::where('uuid', $order_id)->first();

        // store order area
        $area = new OrderArea;
        $area->order_id = $order_id;
        $area->worker_id = auth()->user()->id;
        $area->length = $request->length;
        $area->width = $request->width;
        $area->total_amount = totalCostPerSqft($request->length, $request->width);
        $area->customer_response = 'PENDING';
        $area->save();

        return $this->sendResponse($area, 'Order Area Submitted.');
    }

    public function store(OrderRequest $request)
    {
        $req = Order::create([
            'uuid' => unique_serial('orders', 'uuid', null),
            'user_id' => auth()->user()->id,
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
    public function show(Request $request, $order_id)
    {
        $order = Order::where('uuid', $order_id)->first();
        return $this->sendResponse(new OrderResource($order), 'Order Details.');
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

    public function customer_quoteAction(Request $request, $order_id)
    {
        $validator = \Validator::make($request->all(), [
            'action' => 'required|in:ACCEPTED,REJECTED,RESUBMIT',
        ]);

        if ($validator->fails()) {
            $valid_errors = $this->formatErrors(['action'], $validator->errors());
            return $this->validationError('Validation Error.', $valid_errors);
        }

        $order = Order::where('uuid', $order_id)->first();

        if ($request->action == 'ACCEPTED') {
            $order->order_area->customer_response = 'ACCEPTED';
            $order->save();
            return $this->sendResponse($order->order_area, 'Order Quote Accepted.');
        } elseif ($request->action == 'RESUBMIT') {
            $order->order_area->customer_response = 'REJECTED';
            $order->save();

            $order->order_status()->delete();
            $order->order_area()->delete();

            $resend_order = $order->replicate();
            $resend_order->uuid = unique_serial('orders', 'uuid', null);
            $resend_order->resent= 1;
            $resend_order->resent_order_id = $order_id;
            $resend_order->save();
            return $this->sendResponse($resend_order, 'Order Resubmitted.');
        }
        
        // return $this->sendResponse($order, 'Order Area Submitted.');
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
