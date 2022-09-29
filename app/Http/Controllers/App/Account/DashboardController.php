<?php

namespace App\Http\Controllers\App\Account;

use App\Models\Order;
use App\Models\GeoLocation;
use Illuminate\Support\Str;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResourceCollection;

class DashboardController extends Controller
{
    // dashboard
    public function index(Request $request)
    {
        $orders_count = Order::listing()->orderBy('id', 'desc')->count();
        $orders = Order::listing()->orderBy('id', 'desc')->limit(20)->get();
        $new_notifications = Notification::where('to_user_id',auth()->user()->id)->where('seen', 0)->count();
        
        $data=[
            'total_recent_requests' =>$orders_count,
            'recent_requests' =>  new OrderResourceCollection($orders),
            'new_notifications' => $new_notifications,
        ];

        return $this->sendResponse($data, 'Orders Listing.');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function stateUpdate(Request $request)
    {
        $validator=\Validator::make($request->all(),[
            'state'=>'required|in:active,inactive',
        ]);
        
        if($validator->fails()){
            $valid_errors = $this->formatErrors(['state'], $validator->errors());
            return $this->validationError('Validation Error.', $valid_errors);
        }

        $user = auth()->user();
        $user->state = Str::upper($request->state);
        $user->save();
        return $this->sendResponse([], 'User is '.$request->state);
    }

    
    public function locationUpdate(Request $request)
    {
        $validator=\Validator::make($request->all(),[
            'lat'=>'required',
            'lng'=>'required',
            'city'=>'required',
            'state'=>'required',
            'country'=>'required',
            'full_address'=>'required',
        ]);
        
        if($validator->fails()){
            $fields1=new GeoLocation;
            $fields=$fields1->getFillable();
            $valid_errors = $this->formatErrors( $fields, $validator->errors());
            return $this->validationError('Validation Error.', $valid_errors);
        }

        try {
            $geo=GeoLocation::updateOrCreate([
                'user_id'=>auth()->user()->id
            ],$request->all());
        } catch (\Throwable $th) {
            return $this->validationError('Something went wrong', []);
        }
        
        return $this->sendResponse([], 'User location updated');
    }

    public function getWorkers(Request $request)
    {
        $validator=\Validator::make($request->all(),[
            'lat'=>'required',
            'lng'=>'required',
        ]);
        
        if($validator->fails()){
            $valid_errors = $this->formatErrors( ['lat','lng'], $validator->errors());
            return $this->validationError('Validation Error.', $valid_errors);
        }

        $workers=[];
        $geo_ls=GeoLocation::has('user')->whereHas('user',function($q){
            $q->where('state','ACTIVE');
        })->get();
        foreach ($geo_ls as $key => $g) {
            if (distance($request->lat, $request->lng, $g->lat, $g->lng)) {
                $workers[]=$g; 
            }
        }

        return $this->sendResponse($workers, 'Workers');
    }
}
