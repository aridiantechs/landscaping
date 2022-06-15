<?php

namespace App\Http\Controllers\App\Account;

use App\Models\Notification;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Services\PaymentService;
use App\Models\SquareCustomerCard;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;


class SubscriptionController extends Controller
{
    // store customer card
    public function storeCustomerCard($request)
    {
        $user = auth()->user();
        $data=[
            'user'=>$user,
            'customer_id' => $user->square_customer_id,
            'payment_token'=>$request['payment_token'],
        ];

        $ps=new PaymentService;
        $ps_res=$ps->create_card($data);
        if (!is_null($ps_res) && isset($ps_res['card_id'])) {
            $scc=new SquareCustomerCard;
            $scc->user_id=$user->id;
            $scc->card_id='jhn126387n2723n9123';
            $scc->save();
            return $this->sendResponse($scc, 'Card added successfully.');
        }else{
            return $this->validationError('Something went wrong!', $ps_res, 400);
        }
        
    }

    //store customer
    public function storeCustomer($user)
    {
        $ps=new PaymentService;
        $ps_res=$ps->create_customer([
            'email' => $user->email,
            'phone' => $user->phone,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
        ]);
            
        if (!is_null($ps_res) && isset($ps_res['customer_id'])) {
            $user->square_customer_id=$ps_res['customer_id'];
            $user->save();
            return $this->sendResponse($user, 'Customer added successfully.');
        }else{
            return $this->validationError('Something went wrong!', $ps_res, 400);
        }
        
    }

    // create subscription
    public function createCardAndSubscription(Request $request)
    {
        if ($request->payment_token) {
            $user = auth()->user();
            if ($user->activeSubscription) {
                return $this->validationError('You already have an active subscription.', [], 400);
            }

            // if square customer not found, create it
            if(is_null($user->square_customer_id)){
                $this->storeCustomer($user);
                $res=$res->getData();
                
                // if response_code not 200
                if ($res->response_code != 200) {
                    return $res;
                } 
            }

            $data=[
                'user'=>$user,
                'customer_id' => $user->square_customer_id,
            ];

            // if customer card not found, create it
            if (!$user->square_card) {
                $data['payment_token']=$request->payment_token;
                $res=$this->storeCustomerCard($request);
                $res=$res->getData();
                
                // if response_code not 200
                if ($res->response_code != 200) {
                    return $res;
                } 
            }

            $data['card_id']=$user->square_card->card_id;
            
            // create subscription
            $ps=new PaymentService;
            $ps_res=$ps->create_subscription($data);
            if (!is_null($ps_res) && isset($ps_res['subscription_id'])) {
                $user->subscription_id=$ps_res['subscription_id'];
                $user->save();
                return $this->sendResponse($user, 'Subscription created successfully.');
            }
    
            return $this->validationError('Subscription Failed', [], 400);
        } else {
            return $this->validationError('Payment token is required.', [], 400);
        }
        
    }

    // store subscription
    public function storeSubscription(Request $request)
    {
        $user=auth()->user();
        return $this->store__($user);
    }

    public function store__($user)
    {
        if (!$user->square_card) {
            return $this->validationError('You need to add a card first.', [
                'key'=>'card',
                'message'=>'You need to add a card first!',
            ], 400);
        }

        $data=[
            'user'=>$user,
            'customer_id' => $user->square_customer_id,
            'card_id'=>$user->square_card->card_id,
        ];
        
        $ps=new PaymentService;
        $ps_res=$ps->create_subscription($data);
        
        if (!is_null($ps_res) && isset($ps_res['subscription_id'])) {
            $cs=new Subscription;
            $cs->subs_id=$ps_res['subscription_id'];
            $cs->plan_id=$ps_res['plan_id'];
            $cs->customer_id=$ps_res['customer_id'];
            $cs->start_date=$ps_res['start_date'];
            $cs->end_date=$ps_res['end_date'];
            $cs->save();
            return $this->sendResponse(auth()->user(), 'Subscription created successfully.');
        }else{
            return $this->validationError('Subscription Failed',$ps_res, 400);
        }
    }

    public function renewSubscription(Request $request)
    {
        // if request has type and type is invoice.payment_made
        if ($request->type == 'invoice.payment_made') {
            Storage::disk('public')->put('payment_made.txt', json_encode($request->all()));
        }elseif($request->type == 'invoice.canceled')
        {
            Storage::disk('public')->put('canceled.txt', json_encode($request->all()));
        }elseif($request->type == 'invoice.scheduled_charge_failed')
        {
            Storage::disk('public')->put('scheduled_charge_failed.txt', json_encode($request->all()));
        }elseif($request->type == 'subscription.updated')
        {
            Storage::disk('public')->put('subscription_canceled.txt', json_encode($request->all()));
        }
        
    }

}
