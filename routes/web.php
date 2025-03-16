<?php

use App\Http\Controllers\PaypalController;
use App\Http\Controllers\StripeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

//Paypal
Route::post('paypal',[PaypalController::class,'paypal'])->name('paypal');
Route::get('success',[PaypalController::class,'success'])->name('success');
Route::get('cancel',[PaypalController::class,'cancel'])->name('cancel');

//Stripe
Route::get('stripe',[StripeController::class,'index'])->name('stripe.get');
Route::post('stripe',[StripeController::class,'stripe'])->name('stripe');
Route::get('success',[StripeController::class,'success'])->name('stripe.success');
Route::get('cancel',[StripeController::class,'cancel'])->name('stripe.cancel');