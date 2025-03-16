<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;

class StripeController extends Controller
{
    public function index(){
        return view('stripe');
    }

    public function stripe(Request $request){
        $stripe = new \Stripe\StripeClient(config('stripe.stripe_sk'));

        $response = $stripe->checkout->sessions->create([
        'line_items' => [
            [
            'price_data' => [
                'currency' => 'usd',
                'product_data' => ['name' => $request->product_name],
                'unit_amount' => $request->price*100,
            ],
            'quantity' => $request->quantity,
            ],
        ],
        'mode' => 'payment',
        'success_url' => route('stripe.success').'?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => route('stripe.cancel'),
        ]);

        if(isset($response->id) && $response->id !=''){
            session()->put('product_name',$request->product_name);
            session()->put('quantity',$request->quantity);
            session()->put('price',$request->price);
            return redirect($response->url);
        }else{
            return redirect()->route('stripe.cancel');
        }
    }

    public function success(Request $request){
        if(isset($request->session_id)){
            $stripe = new \Stripe\StripeClient(config('stripe.stripe_sk'));
            $response = $stripe->checkout->sessions->retrieve($request->session_id);
            
            Payment::create([
                'payment_id'=>$response->id,
                'product_name'=>session()->get('product_name'),
                'quantity'=>session()->get('quantity'),
                'amount'=>session()->get('price'),
                'currency'=>$response->currency,
                'payer_name'=>$response->customer_details->name,
                'payer_email'=>$response->customer_details->email,
                'payment_status'=>$response->status,
                'payment_method'=>'Stripe',
            ]);

            return "Payment is Successful";

            unset($request->session_id);
            session()->forget('product_name');
            session()->forget('quantity');
            session()->forget('price');
        }else{
            return redirect()->route('stripe.cancel');
        }
    }

    public function cancel(){
        return "Payment is Canceled";
    }
}
