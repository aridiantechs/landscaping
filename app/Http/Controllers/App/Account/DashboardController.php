<?php

namespace App\Http\Controllers\App\Account;

use App\Models\GeoLocation;
use Illuminate\Support\Str;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{

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
        $geo_ls=GeoLocation::has('user')->with('user')->all();
        foreach ($geo_ls as $key => $g) {
            if (distance($request->lat, $request->lng, $g->lat, $g->lng)) {
                $workers[]=$g; 
            }
        }

        return $this->sendResponse($workers, 'Workers');
    }
}
