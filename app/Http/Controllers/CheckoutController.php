<?php
namespace App\Http\Controllers;
use App\Models\Order;
use App\Models\Payment;
use App\Services\OrderService;
use App\Services\PricingService;
use Illuminate\Http\Request;
class CheckoutController {
 public function form(Request $request,PricingService $pricing){
  try{$quote=$pricing->quote($request->session()->get('cart',[]),$request->user()?->business_id,$request->session()->get('coupon_code'));}catch(\DomainException $e){return redirect()->route('cart.index')->withErrors(['cart'=>$e->getMessage()]);}
  if(!$quote['lines'])return redirect()->route('catalogue');return view('shop.checkout',compact('quote'));
 }
 public function submit(Request $request,OrderService $service){
  $data=$request->validate(['business_name'=>'required|string|max:200','contact_name'=>'required|string|max:150','email'=>'required|email|max:200','phone'=>'nullable|string|max:30','line1'=>'required|string|max:250','line2'=>'nullable|string|max:250','city'=>'required|string|max:100','postcode'=>'required|string|max:20','terms'=>'accepted']);
  $cart=$request->session()->get('cart',[]);
  if(!config('store.stripe_secret'))return back()->withErrors(['payment'=>'Stripe is not configured. No order or payment was created.'])->withInput();
  try{$order=$service->createPending($cart,$data,$request->user()?->business_id,$request->user()?->id,$request->session()->get('coupon_code'));}
  catch(\DomainException $e){return back()->withErrors(['cart'=>$e->getMessage()])->withInput();}
  try{
   \Stripe\Stripe::setApiKey(config('store.stripe_secret'));
   $session=\Stripe\Checkout\Session::create([
    'mode'=>'payment','client_reference_id'=>(string)$order->id,'customer_email'=>$order->email,
    'line_items'=>[['price_data'=>['currency'=>'gbp','unit_amount'=>$order->grand_total_pence,'product_data'=>['name'=>'Wholesale order '.$order->order_number]],'quantity'=>1]],
    'success_url'=>route('checkout.success',['token'=>$order->checkout_token]).'?session_id={CHECKOUT_SESSION_ID}',
    'cancel_url'=>route('checkout.cancel',['token'=>$order->checkout_token]),
    'metadata'=>['order_id'=>(string)$order->id],'payment_intent_data'=>['metadata'=>['order_id'=>(string)$order->id]],
   ],['idempotency_key'=>'order-'.$order->id]);
   Payment::create(['order_id'=>$order->id,'provider_session_id'=>$session->id,'status'=>'pending','amount_pence'=>$order->grand_total_pence]);
   $request->session()->forget(['cart','coupon_code']);return redirect()->away($session->url);
  }catch(\Throwable $e){report($e);$service->releaseUnpaid($order);return back()->withErrors(['payment'=>'Could not begin payment. Please try again.']);}
 }
 public function success(string $token){$order=Order::where('checkout_token',$token)->firstOrFail();return view('shop.result',['order'=>$order,'isSuccess'=>true]);}
 public function cancel(string $token){$order=Order::where('checkout_token',$token)->firstOrFail();return view('shop.result',['order'=>$order,'isSuccess'=>false]);}
}
