<?php
namespace App\Http\Controllers;
use App\Models\Order;
use App\Models\Payment;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
class StripeWebhookController {
 public function __invoke(Request $request,OrderService $orderService){
  $secret=config('store.stripe_webhook_secret');if(!$secret)return response('Webhook not configured',503);
  try{$event=\Stripe\Webhook::constructEvent($request->getContent(),$request->header('Stripe-Signature',''),$secret);}
  catch(\Throwable $e){return response('Invalid signature',400);}
  if(!in_array($event->type,['checkout.session.completed','checkout.session.expired','checkout.session.async_payment_failed'],true))return response('ignored',200);
  // Event ID uniqueness makes repeated Stripe deliveries safe.
  try{DB::transaction(function()use($event){
   $inserted=DB::table('payment_events')->insertOrIgnore(['provider'=>'stripe','event_id'=>$event->id,'event_type'=>$event->type,'payload'=>json_encode($event->toArray()),'created_at'=>now(),'updated_at'=>now()]);
   if(!$inserted)return;
   $session=$event->data->object;$orderId=(int)($session->metadata->order_id??$session->client_reference_id??0);
   $order=Order::whereKey($orderId)->lockForUpdate()->first();if(!$order)return;
   $payment=Payment::where('order_id',$order->id)->first();if(!$payment||$payment->provider_session_id!==$session->id)return;
   if($event->type==='checkout.session.completed' && $session->payment_status==='paid' && (int)$session->amount_total===$order->grand_total_pence && strtolower((string)$session->currency)===$order->currency){
    if($order->status==='pending_payment'){$order->update(['status'=>'paid','payment_status'=>'paid','paid_at'=>now()]);$payment->update(['status'=>'paid','provider_payment_id'=>$session->payment_intent??null]);}
   }
   DB::table('payment_events')->where('event_id',$event->id)->update(['processed_at'=>now()]);
  });}catch(\Throwable $e){Log::error('Stripe webhook handling failed',['event'=>$event->id,'exception'=>$e->getMessage()]);return response('retry',500);}
  if(in_array($event->type,['checkout.session.expired','checkout.session.async_payment_failed'],true)){
   $order=Order::find((int)($event->data->object->metadata->order_id??0));if($order)$orderService->releaseUnpaid($order);
  }
  return response('ok',200);
 }
}
