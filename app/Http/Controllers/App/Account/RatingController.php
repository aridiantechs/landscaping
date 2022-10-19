<?php

namespace App\Http\Controllers\App\Account;

use Carbon\Carbon;

use App\Models\Order;

use App\Models\Rating;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class RatingController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function rate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer|exists:orders,uuid',
            'rating' => 'required|numeric|min:1|max:5',
            'comment' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            $valid_errors=$validator->getMessageBag()->toArray();
            $errors=[];

            $car_fields=new Rating;
            $fields=$car_fields->getFillable();
            foreach ($fields as $key) 
            {
                $message="";
                if(isset($valid_errors[$key]))
                {
                    $message= implode("|",$valid_errors[$key]);
                }
                $errors[] = ['key' => $key,'message' => $message];
            }

            return $this->validationError('Validation error',$errors,401 );
        }

        $order=Order::where('uuid',$request->order_id)->first();
        if ($order->rating) {
            return $this->validationError('Order already rated',[],400 );
        }

        $rating=new Rating;
        $rating->order_id=$request->order_id;
        $rating->rating=$request->rating;
        $rating->comment=$request->comment ?? null;
        $rating->save();
        return $this->sendResponse([], 'Rating Saved.');
    }

}
