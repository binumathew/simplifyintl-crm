<?php
namespace App\Helpers\PaymentGateways;


use Stripe\Stripe;
use Log;

use App\Models\User;
use App\Models\UserCreditCard;
use App\Models\UserData;
class Stripepayments {

    private static $gateway;

    public function __construct(){
       self::$gateway = new \Stripe\StripeClient(config('services.stripe.secret'));
    }

    public static function directDebit($billamount,$mandate_id,$user_id,$metadata) {
        $success = false;
        try{
            $stripe         = new \Stripe\StripeClient(config('services.stripe.secret'));
            $user           = User::where('id', $user_id)->first();
            $customer_id    = $user->userDetail->stripe_customer;
            $mandate_object = $stripe->mandates->retrieve(
              $mandate_id,
              []
            );
            $intent = $stripe->paymentIntents->create([
              'payment_method_types' => ['bacs_debit'],
              'payment_method' => $mandate_object->payment_method,
              'customer' => $customer_id,
              'description'=>$metadata['description'],
              'confirm' => true,
              'amount' => $billamount * 100,
              'currency' => $user->country->currency,
              'metadata' => $metadata,
            ]);
            //return $response = (object)['status'=>false,'error'=>json_encode(['Something else happened, completely unrelated to Stripe'])];
            // return $response = (object)['status'=>true,'transaction_id'=>'pi_1IIfrjHyISlbZmVudrjIbSVv','txn_mandate_id'=>$mandate_id];
            $response = (object)['status'=>true,'transaction_id'=>$intent->id,'txn_mandate_id'=>$mandate_id];
            $success = true;
            return $response;
        }catch(\Stripe\Error\Card $e) {
            $error = $e->getJsonBody();

        }catch(\Stripe\Exception\CardException $e) {
            // Card was declined.
            $error = $e->getJsonBody();
        } catch (\Stripe\Exception\RateLimitException $e) {
            // Too many requests made to the API too quickly
            $error = $e->getJsonBody();
        } catch (\Stripe\Exception\InvalidRequestException $e) {
          // Invalid parameters were supplied to Stripe's API
            $error = $e->getJsonBody();
        } catch (\Stripe\Exception\AuthenticationException $e) {
          // Authentication with Stripe's API failed
          // (maybe you changed API keys recently)
            $error = $e->getJsonBody();
        } catch (\Stripe\Exception\ApiConnectionException $e) {
          // Network communication with Stripe failed
            $error = $e->getJsonBody();
        } catch (\Stripe\Exception\ApiErrorException $e) {
          // Display a very generic error to the user, and maybe send
          // yourself an email
            $error = $e->getJsonBody();
        } catch (\Exception $e) {

          $error = ['Something else happened, completely unrelated to Stripe'];
        }
        if($success == false){
            Log::error('stripeDirectDebitPayment',[
                'error' =>   $error,
                'user_id'=>$user_id
            ]);
            $response = (object)['status'=>false,'error'=>json_encode($error)];
            return $response;
        }
    }
    public static function stripeCardPayment($data,$metadata){
      try {
        $stripe        = Stripe::setApiKey(config('services.stripe.secret'));
        $user_id       = $data['user_id'];
        $user          = User::where('id', $user_id)->first();
        $customer_id   = $user->userDetail->stripe_customer;
        $customer_id   = ($customer_id == null || $customer_id == "") ? $user->parent->userDetail->stripe_customer: $customer_id;

        if(isset($data['card_id']) && $data['card_id'] != ''){
          $card_id    = $data['card_id'];
          $card_data  = UserCreditCard::where('id', $card_id)->where('user_id', $user_id)->first();
          if(!$card_data){
              $response = (object)['status'=>false,'error'=>'Invalid card details'];
              return $response;
          }
          $payment_method_id = $card_data->transaction_id;
          
        }else{
          $payment_method_id = json_decode($data['paymentMethod'])->paymentMethod->id;
            if(is_null($customer_id) || $customer_id == ''){
                try{
                    $customer = \Stripe\Customer::create([
                            'name' => $user->name,
                            'description' => '',
                            'email' => $user->email,
                            'payment_method' => $payment_method_id,
                            "address" => ["city" => $user->userDetail->city, "country" => $user->country->short_code, "line1" => $user->userDetail->address, "line2" => "", "postal_code" => $user->userDetail->postal_code, "state" => $user->userDetail->state]
                            ]);
                    $customer_id = $customer->id;
                }catch (\Exception $e) {
                    $error  = $e->getMessage();
                    $response = (object)['status'=>false,'error'=>json_encode($error)];
                    return $response;
                }
            }
            // else{
            //     try{
            //         $newcard = \Stripe\Customer::createSource(
            //           $customer_id,
            //           ['source' => $data['stripeToken']]
            //         );
            //         $txn_pm_id = $newcard->id;
            //     }catch (\Exception $e) {
            //         $error  = $e->getMessage();
            //         $response = (object)['status'=>false,'error'=>json_encode($error)];
            //         return $response;
            //     }
            // }
        }
        $success =false;
        try {
            $intent = \Stripe\PaymentIntent::create([
              'amount' => $data['total_amount'] * 100,
              'currency' => $user->country->currency,
              'customer' => $customer_id,
              'payment_method' => $payment_method_id,
              'off_session' => true,
              'confirm' => true,
              'description' => $data['payment_for'],
              'metadata' => $metadata,
            ]);

            $txn_id      = $intent->id;
            $txn_pm_id   = $intent->payment_method;
            $stripeCard  = $intent->charges->data[0]->payment_method_details->card;
            $card_type   = $stripeCard->network.' ****'.$stripeCard->last4;
            $exp_day     = date('t',strtotime($stripeCard->exp_year.'-'.$stripeCard->exp_month));
            $card_expire = $stripeCard->exp_year.'-'.$stripeCard->exp_month.'-'.$exp_day;

            $card_data = UserCreditCard::updateOrCreate(['user_id' => $user_id, 'card_type' => $card_type, 'card_expiry' => $card_expire, 'transaction_id' => $txn_pm_id, 'gateway' => $data['gateway_id']]);
            UserData::where('user_id', $user_id)->update(['stripe_customer' => $customer_id]);

            $response = (object)['status'=>true,'transaction_id'=>$intent->id,'payment_method_id'=>$card_data->id,'payment_method'=>$txn_pm_id];
            $success  = true;
            return $response;
            // $response = (object)['status'=>true,'transaction_id'=>'pi_1IW1qbEYEzcmhOUx49AE9Qzm','payment_method_id'=>'100229','payment_method'=>'card_1IVv49EYEzcmhOUxA2eaQphR'];

        }catch(\Stripe\Error\Card $e) {
            $error = $e->getJsonBody();

        }catch(\Stripe\Exception\CardException $e) {
            // Card was declined.
            $error = $e->getJsonBody();
        } catch (\Stripe\Exception\RateLimitException $e) {
            // Too many requests made to the API too quickly
            $error = $e->getJsonBody();
        } catch (\Stripe\Exception\InvalidRequestException $e) {
          // Invalid parameters were supplied to Stripe's API
            $error = $e->getJsonBody();
        } catch (\Stripe\Exception\AuthenticationException $e) {
          // Authentication with Stripe's API failed
          // (maybe you changed API keys recently)
            $error = $e->getJsonBody();
        } catch (\Stripe\Exception\ApiConnectionException $e) {
          // Network communication with Stripe failed
            $error = $e->getJsonBody();
        } catch (\Stripe\Exception\ApiErrorException $e) {
          // Display a very generic error to the user, and maybe send
          // yourself an email
            $error = $e->getJsonBody();
        } catch (\Exception $e) {
          // Something else happened, completely unrelated to Stripe
          $error = ['Something else happened, completely unrelated to Stripe'];
        }
        if($success == false){
          Log::error('stripeCardPayment',[
              'error' =>   $error,
              'user_id'=>$user_id
          ]);
          $response = (object)['status'=>false,'error'=>json_encode($error)];
          return $response;
        }
      } catch (\Exception $e) {
        Log::error('stripeCardPayment',[
          'error' =>   $e->getMessage(),
          'user_id'=>$user_id
        ]);
        $response = (object)['status'=>false,'error'=>json_encode($e->getMessage())];
        return $response;
      }
    }
    public static function stripeCardRefund($data,$metadata){
      $stripe   = new \Stripe\StripeClient(config('services.stripe.secret'));
      $success  = false;
      try {
          $refund = $stripe->refunds->create([
            'payment_intent' => $data['transaction_id'],
            'amount' => $data['amount'] * 100,
            'metadata'=>$metadata
          ]);
          $response = (object)['status'=>true,'transaction_id'=>$refund->id];
          $success  = true;
          return $response;
      }catch(\Stripe\Error\Card $e) {
          $error = $e->getJsonBody();

      }catch(\Stripe\Exception\CardException $e) {
          // Card was declined.
          $error = $e->getJsonBody();
      } catch (\Stripe\Exception\RateLimitException $e) {
          // Too many requests made to the API too quickly
          $error = $e->getJsonBody();
      } catch (\Stripe\Exception\InvalidRequestException $e) {
        // Invalid parameters were supplied to Stripe's API
          $error = $e->getJsonBody();
      } catch (\Stripe\Exception\AuthenticationException $e) {
        // Authentication with Stripe's API failed
        // (maybe you changed API keys recently)
          $error = $e->getJsonBody();
      } catch (\Stripe\Exception\ApiConnectionException $e) {
        // Network communication with Stripe failed
          $error = $e->getJsonBody();
      } catch (\Stripe\Exception\ApiErrorException $e) {
        // Display a very generic error to the user, and maybe send
        // yourself an email
          $error = $e->getJsonBody();
      } catch (\Exception $e) {
        // Something else happened, completely unrelated to Stripe
        $error = ['Something else happened, completely unrelated to Stripe'];
      }
      if($success == false){
        Log::error('stripeCardRefund',[
            'error' =>   $error,
            'order_id'=>$metadata['order_id']
        ]);
        $response = (object)['status'=>false,'error'=>json_encode($error)];
        return $response;
      }
    }
    public static function createPaymentMethod($data){
      try {
          $payment_method =   self::$gateway->paymentMethods->create($data);
          return $response = (object)['status'=>true,'payment_method_id'=>$payment_method->id];
      }catch(\Exception $e) {
        Log::error('createPaymentMethod',[
            'error' =>   $e->getMessage(),
            'data'=>$data
        ]);
        return $response = (object)['status'=>false,'error'=>$e->getMessage()];
      }
    }
    public static function createCustomer($data){
        try{
            $result = self::$gateway->customers->create($data);
            Log::info('create-customer',[
                'data'=> json_encode($data),
                'result' =>   json_encode($result)
            ]);
            return (object)['status'=>true,'customerId'=>$result->id];
        }catch(\Exception $e){
            Log::error('create-customer',[
                'data'=> json_encode($data),
                'error' => $e->getMessage()
            ]);
            return (object)['status'=>false,'error'=>$e->getMessage()];
        }
    }
    public static function attachPaymentMethod($customer_id,$payment_method_id){
      try{
            $newcard = self::$gateway->paymentMethods->attach(
                $payment_method_id,
                ['customer' => $customer_id]
            );
        }catch (\Exception $e) {
          Log::error('attach-payment-method',[
                'data'=> json_encode($data),
                'error' => $e->getMessage()
            ]);
            return (object)['status'=>false,'error'=>$e->getMessage()];
        }
    }
    public static function createPaymentIntent($data){
      try{
            $intent = self::$gateway->paymentIntents->create($data);
            Log::info('create-payment-intent',[
                    'data'=> json_encode($data),
                    'result' => json_encode($intent)
                ]);
            $txn_id      =  $intent->id;
            $txn_card_id =  $intent->payment_method;
            $stripeCard  = $intent->charges->data[0]->payment_method_details->card;
            $card_type   = $stripeCard->network.' ****'.$stripeCard->last4;
            $exp_day     = date('t',strtotime($stripeCard->exp_year.'-'.$stripeCard->exp_month));
            $card_expire = $stripeCard->exp_year.'-'.$stripeCard->exp_month.'-'.$exp_day;
            $response = (object)['status'=>true,'transaction_id'=>$intent->id,'token'=>$intent->payment_method,'card_type'=>$card_type,'card_expire'=>$card_expire];
            return $response;
        }catch (\Exception $e) {
          Log::error('create-payment-intent',[
                'data'=> json_encode($data),
                'error' => $e->getMessage()
            ]);
            return (object)['status'=>false,'error'=>$e->getMessage()];
        }
    }

}
