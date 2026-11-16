<?php
namespace App\Http\Controllers;

use App\Mail\OrderDispatched;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/** V16: status and optional dispatch tracking are submitted in one transaction. */
class AdminOrderFulfilmentController {
 public function update(Request $request,Order $order) {
  $data=$request->validate([
   'status'=>'required|in:processing,dispatched,delivered',
   'carrier'=>'nullable|string|max:120',
   'tracking_number'=>'nullable|string|max:180',
  ]);
  $sendDispatch=false;
  DB::transaction(function()use($request,$order,$data,&$sendDispatch){
   $locked=Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
   abort_unless($locked->payment_status==='paid',422,'Only paid orders can be fulfilled.');
   abort_if($locked->status==='cancelled',422,'Cancelled orders cannot be fulfilled.');
   abort_if($locked->status==='delivered' && $data['status']!=='delivered',422,'Delivered orders cannot be moved backwards.');
   abort_if($locked->status==='dispatched' && $data['status']==='processing',422,'Dispatched orders cannot be moved backwards.');
   $prior=$locked->status;
   if($data['status']==='dispatched'){
    $carrier=trim((string)($data['carrier']??'')) ?: null;
    $tracking=trim((string)($data['tracking_number']??'')) ?: null;
    if($carrier!==null || $tracking!==null){
     $existing=DB::table('shipments')->where('order_id',$locked->id)->first();
     if($existing){DB::table('shipments')->where('id',$existing->id)->update(['carrier'=>$carrier,'tracking_number'=>$tracking,'dispatched_at'=>$existing->dispatched_at??now(),'updated_at'=>now()]);}
     else DB::table('shipments')->insert(['order_id'=>$locked->id,'carrier'=>$carrier,'tracking_number'=>$tracking,'dispatched_at'=>now(),'created_at'=>now(),'updated_at'=>now()]);
    }
    if($prior!=='dispatched' && $locked->dispatch_email_sent_at===null){
     // Claim notification in the order lock; repeat saves do not claim again.
     $locked->dispatch_email_status='pending';
     $sendDispatch=true;
    }
   }
   if($data['status']==='delivered'){
    DB::table('shipments')->where('order_id',$locked->id)->update(['delivered_at'=>now(),'updated_at'=>now()]);
   }
   $locked->status=$data['status'];$locked->save();
   DB::table('audit_logs')->insert(['user_id'=>$request->user()->id,'action'=>'order.fulfilment.updated','subject_type'=>'order','subject_id'=>$locked->id,'changes'=>json_encode(['before'=>$prior,'after'=>$data['status'],'carrier'=>$data['carrier']??null,'has_tracking'=>!empty($data['tracking_number'])]),'created_at'=>now(),'updated_at'=>now()]);
  });
  if($sendDispatch){
   return $this->deliverNotification($order, false);
  }
  return back()->with('success','Fulfilment status saved. No additional dispatch email sent.');
 }
 /** Compatibility for old tracking route; the V16 UI uses update() only. */
 public function tracking(Request $request,Order $order){
  $request->merge(['status'=>'dispatched']);
  return $this->update($request,$order);
 }
 /** Manual recovery for mail failures; requires an explicit admin action. */
 public function retryNotification(Request $request, Order $order){
  abort_unless($order->payment_status==='paid' && $order->status==='dispatched',422,'This order is not dispatched and paid.');
  return $this->deliverNotification($order,true);
 }
 private function deliverNotification(Order $order,bool $explicitRetry){
  $claimed=false;
  DB::transaction(function()use($order,$explicitRetry,&$claimed){
   $locked=Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
   if($locked->payment_status!=='paid' || $locked->status!=='dispatched' || $locked->dispatch_email_sent_at!==null)return;
   if($locked->dispatch_email_status==='sending' && $locked->dispatch_email_claimed_at && $locked->dispatch_email_claimed_at->gt(now()->subMinutes(10)))return;
   // If a crash interrupts delivery, an admin can retry after the claim expires.
   if(!$explicitRetry && $locked->dispatch_email_status==='failed')return;
   $locked->dispatch_email_status='sending';
   $locked->dispatch_email_claimed_at=now();
   $locked->save();$claimed=true;
  });
  if(!$claimed)return back()->with('success','Status saved. The dispatch notification was already sent or is being handled.');
  try {
   $shipment=DB::table('shipments')->where('order_id',$order->id)->latest('id')->first();
   Mail::to($order->email)->send(new OrderDispatched($order->fresh(), $shipment?->carrier, $shipment?->tracking_number));
   Order::whereKey($order->id)->update(['dispatch_email_status'=>'sent','dispatch_email_sent_at'=>now(),'dispatch_email_claimed_at'=>null]);
   return back()->with('success','Order dispatched and customer confirmation email sent.');
  }catch(\Throwable $e){
   Order::whereKey($order->id)->update(['dispatch_email_status'=>'failed','dispatch_email_claimed_at'=>null]);
   Log::error('Dispatch notification failed',['order_id'=>$order->id,'error'=>$e->getMessage()]);
   return back()->with('warning','Order status saved, but the dispatch email failed. Check mail configuration, then use Retry dispatch email.');
  }
 }
}
