<?php

namespace App\Services;
use Carbon\Carbon;
use Square\Environment;
use Square\SquareClient;
use Square\Exceptions\ApiException;

class PaymentService{

    protected $client;
	// square payment
    // construct
    public function __construct()
    {
        $this->client = new SquareClient([
            'accessToken' => config('app.SQUARE_ACCESS_TOKEN'),
            'environment' => Environment::SANDBOX,
        ]);
    }

    // get locations
    public function getLocations()
    {
        try {
            $apiResponse = $this->client->getLocationsApi()->listLocations();

            if ($apiResponse->isSuccess()) {
                $result = $apiResponse->getResult();
                foreach ($result->getLocations() as $location) {
                    printf(
                        "%s: %s, %s, %s<p/>", 
                        $location->getId(),
                        $location->getName(),
                        $location->getAddress()->getAddressLine1(),
                        $location->getAddress()->getLocality()
                    );
                }

            } else {
                $errors = $apiResponse->getErrors();
                foreach ($errors as $error) {
                    printf(
                        "%s<br/> %s<br/> %s<p/>", 
                        $error->getCategory(),
                        $error->getCode(),
                        $error->getDetail()
                    );
                }
            }
        } catch (ApiException $e) {
            echo "ApiException occurred: <b/>";
            echo $e->getMessage() . "<p/>";
        }

    }

    // create customer
    public function create_customer($data = array()){

        $body = new \Square\Models\CreateCustomerRequest();
        $body->setGivenName($data['first_name']);
        $body->setFamilyName($data['last_name']);
        $body->setEmailAddress($data['email']);
        $body->setPhoneNumber($data['phone']);

        $api_response = $this->client->getCustomersApi()->createCustomer($body);

        if ($api_response->isSuccess()) {
            $result = $api_response->getResult();
            $result=[
                'customer_id'=>$result->getCustomer()->getId(),
                'first_name'=>$result->getCustomer()->getGivenName(),
                'last_name'=>$result->getCustomer()->getFamilyName(),
            ];
        } else {
            $result = $api_response->getErrors();
            $result=[
                'error'=>$result,
            ];
        }
        return $result;
    }

    // create card
    public function create_card($data = array()){

        $billing_address = new \Square\Models\Address();
        $billing_address->setAddressLine1('500 Electric Ave');
        $billing_address->setAddressLine2('Suite 600');
        $billing_address->setLocality('New York');
        $billing_address->setAdministrativeDistrictLevel1('NY');
        $billing_address->setPostalCode('94103');
        $billing_address->setCountry('US');

        $card = new \Square\Models\Card();
        $card->setCardholderName('Jane Doe');
        $card->setBillingAddress($billing_address);
        $card->setCustomerId($data['customer_id']);
        $card->setReferenceId('test-id-1');

        $body = new \Square\Models\CreateCardRequest(
            uniqid(),
            $data['payment_token'],
            $card
        );

        $api_response = $this->client->getCardsApi()->createCard($body);

        if ($api_response->isSuccess()) {
            $result = $api_response->getResult();
            $result=[
                'card_id'=>$result->getCard()->getId(),
            ];
        } else {
            $result = $api_response->getErrors();
            $result=[
                'error'=>$result,
            ];
        }

        return $result;
    }

    // create subscription
    public function create_subscription($data = array()){

        $body = new \Square\Models\CreateSubscriptionRequest(
            'L5AHC9DNN4ZQB',
            '7T4MEZ35TYX7W7AIGSAFDYGO',
            $data['customer_id'],
        );
        $body->setIdempotencyKey(uniqid());
        $body->setCardId($data['card_id']);
        
        $api_response = $this->client->getSubscriptionsApi()->createSubscription($body);
        
        if ($api_response->isSuccess()) {
            $result = $api_response->getResult();
            $result=[
                'subscription_id'=>$result->getSubscription()->getId(),
                'plan_id'=>$result->getSubscription()->getPlanId(),
                'customer_id'=>$result->getSubscription()->getCustomerId(),
                'start_date'=>$result->getSubscription()->getStartDate(),
                'end_date'=>Carbon::parse($result->getSubscription()->getStartDate())->addMonth()->format('Y-m-d H:i:s'),
            ];
        } else {
            $result = $api_response->getErrors();
            $result=[
                'error'=>$result,
            ];
        }

        return $result;
    }
        
}