<?php

namespace App\Http\Controllers\App\Account;

use App\Models\User;
use App\Models\Notification;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Services\PaymentService;
use App\Models\SquareCustomerCard;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Storage;
use Spatie\SlackAlerts\Facades\SlackAlert;


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
            $scc->card_id= $ps_res['card_id'];
            $scc->save();
            return $this->sendResponse((object)$scc, 'Card added successfully.');
        }else{
            return $this->validationError('Something went wrong!', (object)$ps_res, 400);
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
            return $this->sendResponse((object)$user, 'Customer added successfully.');
        }else{
            return $this->validationError('Something went wrong!', (object)$ps_res, 400);
        }
        
    }

    // create subscription
    public function createCardAndSubscription(Request $request)
    {
        createLog(
            'CREATE_SUBSCRIPTION',
            [
                "request" => $request->all()
            ]
        );
        // NotificationService::slack("```".json_encode($request->all())."```");
        if ($request->payment_token) {
            $user = auth()->user();
            if ($user->activeSubscription) {
                return $this->validationError('You already have an active subscription.', (object)[], 400);
            }

            // if square customer not found, create it
            if(is_null($user->square_customer_id)){
                $res = $this->storeCustomer($user);
                $res=$res->getData();
                // if response_code not 200
                if ($res->response_code != 200) {
                    // NotificationService::slack("Failed to get or create user in SQUARE");
                    return $res;
                } 
            }

            $data=[
                'user'=>$user,
                'customer_id' => $user->square_customer_id,
            ];

            // if trial not active store new card
            if ($user->square_card && $user->trialEndedOrNoSubscription()) {
                $user->square_card()->delete();
            }

            // if customer card not found, create it
            if (!$user->square_card()->first()) {
                // NotificationService::slack("Storing Customer {$user->email}");
                $data['payment_token']=$request->payment_token;
                $res=$this->storeCustomerCard($request);
                $res=$res->getData();
                
                // if response_code not 200
                if ($res->response_code != 200) {
                    // NotificationService::slack("Card Failed ```".json_encode($res)."```");
                    return $res;
                } 
                // NotificationService::slack("Card Added ```".json_encode($res)."```");
            }

            $data['card_id']=$user->square_card()->first()->card_id;
            
            // create subscription
            $ps=new PaymentService;
            if ($user->trialEndedOrNoSubscription()) {
                $ps_res=$ps->create_subscription($data);
            } else {
                $ps_res=$ps->swap_subscription_plan($user->lastSubscription->subs_id);
            }
            

            if (!is_null($ps_res) && isset($ps_res['subscription_id'])) {

                // NotificationService::slack("Subscription Created ```".json_encode($ps_res)."```");

                $cs=new Subscription;
                $cs->subs_id=$ps_res['subscription_id'];
                $cs->plan_id=$ps_res['plan_id'];
                $cs->customer_id=$ps_res['customer_id'];
                $cs->start_date=$ps_res['start_date'];
                $cs->end_date=$ps_res['end_date'];
                $cs->trial_end_at=$ps_res['trial_end_at'] ?? '';
                $cs->status='ACTIVE';
                $cs->save();

                
                $user=User::find(auth()->user()->id);
                return $this->sendResponse(new UserResource($user), 'Subscription created successfully.',200,(object)[]);
            }
        
            // NotificationService::slack("Failed to create subscription ```".json_encode($ps_res)."```");
            return $this->validationError('Subscription Failed', (object)[], 400);
        } else {
            return $this->validationError('Payment token is required.', (object)[], 400);
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

    public function subscriptionWebhook(Request $request)
    {

        createLog('SQUARE_WEBHOOK',[
            'payload' => $request->all(),
        ]);

        // if request has type and type is invoice.payment_made
        if ($request->type == 'invoice.payment_made') 
        {
            if ($request->data && ($request->data['object'] ?? false) && ($request->data['object']['invoice'] ?? false)) {
                $invoice=$request->data['object']['invoice'];
                $inv_subs=Subscription::where('customer_id',$invoice['primary_recipient']['customer_id'])->first();
                if (!$inv_subs) {
                    $ps_res= $this->getSubscription($invoice['subscription_id']);
                    if (!is_null($ps_res) && isset($ps_res['subscription_id'])) {
                        $cs = new Subscription;
                        $cs->subs_id=$ps_res['subscription_id'];
                        $cs->plan_id=$ps_res['plan_id'];
                        $cs->customer_id=$ps_res['customer_id'];
                        $cs->start_date=$ps_res['start_date'];
                        $cs->end_date=$ps_res['end_date'];
                        $cs->status='ACTIVE';
                        $cs->save();

                        createLog('SQUARE_INVOICE_PAYMENT_MADE',[
                            'square_payload' => $request->all(),
                        ]);

                    }
                }
                
            }
        }elseif($request->type == 'invoice.canceled')
        {
            createLog('SQUARE_INVOICE_CANCELLED',[
                'square_payload' => $request->all(),
            ]);
            // Storage::disk('public')->put('canceled.txt', json_encode($request->all()));
        }elseif($request->type == 'invoice.scheduled_charge_failed')
        {
            if ($request->data && ($request->data['object'] ?? false) && ($request->data['object']['invoice'] ?? false)) {
                $invoice=$request->data['object']['invoice'];
                $inv_subs=Subscription::where('subs_id',$invoice->subscription_id)->first();
                if ($inv_subs) {
                    $inv_subs->status='RENEWAL_FAILED';
                    $inv_subs->save();
                }
                createLog('SQUARE_INVOICE_RENEWAL_FAILED',[
                    'square_payload' => $request->all(),
                ]);
            }
        }elseif($request->type == 'subscription.updated')
        {
            if ($request->data && ($request->data['object'] ?? false) && ($request->data['object']['subscription'] ?? false)) {
                $subs=$request->data['object']['subscription'];
                $inv_subs=Subscription::where('subs_id',$subs->id)->first();
                if ($inv_subs) {
                    $inv_subs->status=$subs->status;
                    $inv_subs->save();
                }
            }
            createLog('SQUARE_SUBSCRIPTION_UPDATED',[
                'square_payload' => $request->all(),
            ]);
        }
        
    }

    public function getSubscription($subscription_id)
    {
        // fetch subscription
        $ps=new PaymentService;
        return $ps_res=$ps->get_subscription($subscription_id);
        
    }

    public function cancelSubscription(Request $request)
    {
        if (auth()->user()->square_customer_id && (auth()->user()->activeSubscription || auth()->user()->dayOldSubscription()) ) {
            $subscription=auth()->user()->activeSubscription ? auth()->user()->activeSubscription : auth()->user()->dayOldSubscription();
            // cancel subscription
            $ps=new PaymentService;
            $ps_res=$ps->cancel_subscription($subscription->subs_id);
            
            if (!is_null($ps_res) && isset($ps_res['cancelled_at'])) {
                
                $subscription->cancel_date=$ps_res['cancelled_at'];
                $subscription->status='CANCELLED';
                $subscription->save();
                return $this->sendResponse($subscription, 'Subscription canceled successfully.');
            }else{
                return $this->validationError('Subscription Failed',$ps_res, 400);
            }
        } else {
            return $this->validationError('You need to have an active subscription to cancel.', [], 400);
        }
        
        
    }

}
