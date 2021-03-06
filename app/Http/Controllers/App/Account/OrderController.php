<?php

namespace App\Http\Controllers\App\Account;

use App\Models\Order;
use App\Models\OrderArea;
use App\Models\UserDevice;
use App\Models\GeoLocation;
use App\Models\OrderStatus;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Models\OrderResponse;
use App\Http\Requests\OrderRequest;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\OrderResourceCollection;

class OrderController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $orders = Order::listing();
        if($request->query('search')){
            
            $orders = $orders->where(function($q) use ($request){
                $q->where('uuid','LIKE','%'.trim($request->query('search')).'%');
            });
        }
        $orders = $orders->get();
        return $this->sendResponse(new OrderResourceCollection($orders), 'Orders Listing.');
    }

    public function worker_action(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,uuid',
            'status' => 'required|in:ACCEPTED,REJECTED,SCHEDULE',
        ]);

        if ($validator->fails()) {
            $valid_errors = $this->formatErrors(['order_id','status'], $validator->errors());
            return $this->validationError('Validation Error.', $valid_errors);
        }

        $order = Order::where('uuid', $request->order_id)->first();
        if (auth()->user()->hasAcceptedOrder()) {
            return $this->validationError('You are already to an order, and its not completed !', []);
        }
        
        if ( $order->accepted_response && $order->accepted_response->count()) {
            return $this->validationError('Order already accepted.', []);
        }
        elseif($order->accepted_response_user()){
            return $this->validationError('You are not authorized to perform this action again.', []);
        }
        $order_r = new OrderResponse;
        $order_r->order_id = $request->order_id;
        $order_r->user_id = auth()->user()->id;
        $order_r->response_user = auth()->user()->roles()->first()->name ?? '';
        $order_r->response_type = $request->status;
        $order_r->save();
        if ($request->status == 'ACCEPTED') {
            $order_s = new OrderStatus;
            $order_s->order_id = $request->order_id;
            $order_s->worker_id = auth()->user()->id;
            $order_s->status = 'PENDING';
            $order_s->save();
        }

        $user_devices = UserDevice::where('user_id',$order->user_id)->whereNotNull('device_id')->pluck('device_id')->toArray();
        // dd($user_devices);
        if (in_array($request->status, array('ACCEPTED'))) {
            $data=[
                'type'=>"Request Action",
                'to_role'=>"endUser",
                'req_id'=>$order->id,
                'order_address'=>$order->full_address,
                'to_user_id'=> $order->user_id,
                'title'=> "Order Update !",
                'body'=> "Your Order has been ".$request->status." by ".auth()->user()->name,
                'object'=> json_encode(['req_id' => $order->id,'order_r'=>$order_r]),
                'dimensions_submitted'=>false
                
            ];
            
            NotificationService::send($user_devices,$data);
        } 
        
        
        return $this->sendResponse(new OrderResource($order), 'Order Status Updated.');
    }

    public function customer_action(Request $request){
        $validator = \Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,uuid',
            'worker_id' => 'required|exists:users,id',
            'status' => 'required|in:ACCEPTED,REJECTED',
        ]);

        
        if ($validator->fails()) {
            $valid_errors = $this->formatErrors(['order_id', 'worker_id', 'status'], $validator->errors());
            return $this->validationError('Validation Error.', $valid_errors);
        }

        $order = Order:: where('uuid', $request->order_id)->first();
        $order_r = $order->order_responses->where('user_id', $request->worker_id)->where('response_type', 'SCHEDULE')->first();
        if (!$order_r) {
            return $this->validationError('Order response not found.', []);
        }

        if ($request->status=='ACCEPTED') {
            $order_r = new OrderStatus;
            $order_r->order_id = $request->order_id;
            $order_r->worker_id = $request->worker_id;
            $order_r->status = 'PENDING';
            $order_r->save();

            $user_devices = UserDevice::where('user_id',$request->worker_id)->whereNotNull('device_id')->pluck('device_id')->toArray();
            if ($user_devices && count($user_devices)) {
                $data=[
                    'type'=>"Customer Request Action",
                    'to_role'=>"worker",
                    'req_id'=>$order->id,
                    'to_user_id'=> $request->worker_id,
                    'title'=> "Order Schedule accepted !",
                    'body'=> "Order schedule accepted by ".auth()->user()->name,
                    'object'=> json_encode(['req_id' => $order->id]),
                    'dimensions_submitted'=>false
                    
                ];
                
                NotificationService::send($user_devices,$data);
            }

            return $this->sendResponse(new OrderResource($order), 'Order Status Updated.');
        }else{
            return $this->sendResponse([], 'Response Rejected.');
        }
        
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

        $order = Order::where('uuid', $order_id)->first();
        if($order->hasBeenScheduled()){
            return $this->validationError('Order already scheduled.', []);
        }

        $order_r = OrderResponse::where('order_id', $order_id)->where('user_id', auth()->user()->id)->first();
        if ($order_r && $order_r->response_type && $order_r->response_type == 'SCHEDULE') {
            $order_r->time = $request->time;
            $order_r->comments = $request->comments;
            $order_r->save();

            $user_devices = UserDevice::where('user_id',$order->user_id)->whereNotNull('device_id')->pluck('device_id')->toArray();
            $data=[
                'type'=>"Request Schedule",
                'to_role'=>"endUser",
                'req_id'=>$order->id,
                'order_response_id'=>$order_r->id,
                'schedule_time'=>$order_r->time,
                'schedule_comments'=>$order_r->comments,
                'to_user_id'=> $order->user_id,
                'title'=> "Order Update !",
                'body'=> "Your Order has been scheduled by ".auth()->user()->name." at ".$request->time,
                'object'=> json_encode(['req_id' => $order->id,'order_r'=>$order_r]),
                'dimensions_submitted'=>false
                
            ];
            // dd($data);
            $notify=NotificationService::send($user_devices,$data);

            return $this->sendResponse(new OrderResource($order_r->order), 'Order Schedule Updated.');
        } else {
            return $this->validationError('Validation Error.', 'Order Response not found or cannot be SCHEDULE');
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
        if (!$order) {
            return $this->validationError('Order not found.', []);
        }

        if ( $order->userSubmittedArea) {
            return $this->validationError("You can't perform this action again", []);
        }
        // store order area
        $area = new OrderArea;
        $area->order_id = $order_id;
        $area->worker_id = auth()->user()->id;
        $area->length = $request->length;
        $area->width = $request->width;
        $area->total_amount = totalCostPerSqft($request->length, $request->width);
        $area->customer_response = 'PENDING';
        $area->save();

        $user_devices = UserDevice::where('user_id',$order->user_id)->whereNotNull('device_id')->pluck('device_id')->toArray();
        if ($user_devices && count($user_devices)) {
            $data=[
                'type'=>"Worker Dimention Submit",
                'to_role'=>"endUser",
                'req_id'=>$order->id,
                'order_id'=>$order->uuid,
                'to_user_id'=> $order->user_id,
                'title'=> "Dimentions Submitted !",
                'body'=> "Dimentions of the property have been submitted by ".auth()->user()->name,
                'object'=> json_encode(['req_id' => $order->id]),
                'dimensions_submitted'=>true
            ];
            
            NotificationService::send($user_devices,$data);
        }

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
        $this->sendNotification($req);

        return $this->sendResponse(new OrderResource($req), 'Order Created.');
    }

    public function sendNotification($req)
    {
        $workers=[];
        $geo_ls=GeoLocation::whereHas('user',function($q){
            $q->where('state','ACTIVE');
        })->with('user')->get();

        foreach ($geo_ls as $key => $g) {
            if (distance($req->lat, $req->lng, $g->lat, $g->lng)) {
                $workers[]=$g->user->id; 
            }
        }

        $user_devices = UserDevice::whereIn('user_id',$workers)->get()->pluck('device_id')->toArray();
        $user_devices = array_values(array_filter($user_devices));
        // dd($user_devices);
        $data=[
            'type'=>"Request",
            'to_role'=>"worker",
            'req_id'=>$req->id,
            'order_address'=>$req->full_address,
            'to_user_id'=> $workers,
            'title'=> 'You have recieved a request !',
            'body'=> $req->additional_info,
            'object'=> json_encode(['req_id' => $req->id]),
            'dimensions_submitted'=>false
            
        ];
        // dd($data);
        NotificationService::send($user_devices,$data);
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
        if (!$order || ($order && !$order->order_area)) {
            return $this->validationError('Order/ order area not found.', []);
        }

        if ( $order->hasCustomerResponse) {
            return $this->validationError("You can't perform this action again", []);
        }

        $order_a = OrderArea::where('order_id', $order_id)->first();
        $user_devices = UserDevice::where('user_id',$order_a->worker_id)->whereNotNull('device_id')->pluck('device_id')->toArray();
        
        if ($request->action == 'ACCEPTED') {
            $order_a->customer_response = 'ACCEPTED';
            $order_a->save();
        }elseif($request->action == 'REJECTED'){
            $order_a->customer_response = 'REJECTED';
            $order_a->save();
            $order->order_status()->delete();
            $order->order_area()->delete();
            return $this->sendResponse($order->order_area, 'Order Quote Rejected.');
        }elseif ($request->action == 'RESUBMIT') {
            $order_a->order_area->customer_response = 'REJECTED';
            $order_a->save();

            $order->order_status()->delete();
            $order->order_area()->delete();

            $resend_order = $order->replicate();
            $resend_order->uuid = unique_serial('orders', 'uuid', null);
            $resend_order->resent= 1;
            $resend_order->resent_order_id = $order_id;
            $resend_order->save();
            return $this->sendResponse($resend_order, 'Order Resubmitted.');
        }

        if ($user_devices && count($user_devices)) {
            $data=[
                'type'=>"Customer Dimention Submit",
                'to_role'=>"worker",
                'req_id'=>$order->id,
                'to_user_id'=> $order_a->worker_id,
                'title'=> "Order qoute accepted !",
                'body'=> "Customer has accepted your quote, you can start working on the order.",
                'object'=> json_encode(['req_id' => $order->id]),
                'dimensions_submitted'=>true
                
            ];
            
            NotificationService::send($user_devices,$data);
        }
        return $this->sendResponse($order_a, 'Order Quote Accepted.');
        // return $this->sendResponse($order, 'Order Area Submitted.');
    }

    public function complete(Request $request, $order_id)
    {
        $order = Order::where('uuid', $order_id)->first();
        if($order && $order->order_status){
            $order->order_status->status = 'COMPLETED';
            $order->order_status->save();

            return $this->sendResponse(new OrderResource($order), 'Order Details.');
        }else{
            return $this->validationError('Order not found.', []);
        }

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
