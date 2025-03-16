<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PaypalClient;
use App\Models\Payment;

class PaypalController extends Controller
{
    public function paypal(Request $request)
    {
        $provider = new PaypalClient();
        $provider->setApiCredentials(config('paypal'));
        $paypalToken = $provider->getAccessToken();
        $response = $provider->createOrder([
            "intent" => "CAPTURE",
            "application_context"=> [
                "return_url"=> route('success'),
                "cancel_url"=> route('cancel')
            ],
            "purchase_units" => [
                [
                    "amount" => [
                        "currency_code" => "USD",
                        "value" => $request->price
                    ]
                ]
            ]
        ]);

        if(isset($response['id']) && $response['id']!=null){
            foreach($response['links'] as $link){
                if($link['rel'] === 'approve'){
                    session()->put('product_name',$request->product_name);
                    session()->put('quantity',$request->quantity);
                    return redirect()->away($link['href']);
                }
            }
        }else{
            return redirect()->route('cancel');
        }
    }

    public function success(Request $request) {
        $provider = new PaypalClient();
        $provider->setApiCredentials(config('paypal'));
        $paypalToken = $provider->getAccessToken();
        $response = $provider->capturePaymentOrder($request->token);
        
        if(isset($response['status']) && $response['status'] == 'COMPLETED'){
            //Insert data into database
            
            Payment::create([
                'payment_id'=>$response['id'],
                'product_name'=>session()->get('product_name'),
                'quantity'=>session()->get('quantity'),
                'amount'=>$response['purchase_units'][0]['payments']['captures'][0]['amount']['value'],
                'currency'=>$response['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code'],
                'payer_name'=>$response['payer']['name']['given_name'].' '.$response['payer']['name']['surname'],
                'payer_email'=>$response['payer']['email_address'],
                'payment_status'=>$response['status'],
                'payment_method'=>'PayPal',
            ]);

            return "Payment is Successfully!!!";

            unset($_SESSION['product_name']);
            unset($_SESSION['quantity']);
        }else{
            return redirect()->route('cancel');
        }

    }

    public function cancel() {
        return "Payment is Cancelled!!!";
    }
}
