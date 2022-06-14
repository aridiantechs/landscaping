<?php

namespace App\Http\Controllers\App\Account;

use App\Models\Notification;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Services\PaymentService;
use App\Models\SquareCustomerCard;
use App\Http\Controllers\Controller;


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
            $scc->card_id=$ps_res['card_id'];
            $scc->save();
            return $this->sendResponse($scc, 'Card added successfully.');
        }

        return $this->validationError('Something went wrong!', [], 400);
        
    }

    // create subscription
    public function createSubscription(Request $request)
    {
        if ($request->payment_token) {
            $user = auth()->user();
            if ($user->activeSubscription) {
                return $this->validationError('You already have an active subscription.', [], 400);
            }elseif($user->square_customer_id){
                $data=[
                    'user'=>$user,
                    'customer_id' => $user->square_customer_id,
                ];

                if (!$user->square_card) {
                    $data['payment_token']=$request->payment_token;
                    $res=$this->storeCustomerCard($request);
                    // if response_code not 200
                    if ($res['response_code'] != 200) {
                        return $res;
                    } 
                }

                $data=[
                    'card_id'=>$user->square_card->card_id,
                ];
        
                $ps=new PaymentService;
                $ps_res=$ps->create_subscription($data);
                if (!is_null($ps_res) && isset($ps_res['subscription_id'])) {
                    $user->subscription_id=$ps_res['subscription_id'];
                    $user->save();
                    return $this->sendResponse($user, 'Subscription created successfully.');
                }
        
                return $this->validationError('Subscription Failed', [], 400);
            }
        } else {
            return $this->validationError('Payment token is required.', [], 400);
        }
        
    }

    // store subscription
    public function storeSubscription(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->square_card) {
            return $this->validationError('You need to add a card first.', [], 400);
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
        }
        return $this->validationError('Subscription Failed', [], 400);
    }

}
