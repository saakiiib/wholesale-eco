@extends('portal.shell',['portal'=>'admin'])
@section('title','Manage order '.$order->order_number)
@push('styles')
<style>
.v16-dispatch-panel{border:1px solid #cfe2d0;background:#f5f9f4;border-radius:12px;padding:16px;margin:12px 0}
.v16-dispatch-panel[hidden]{display:none}
.v16-dispatch-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.v16-dispatch-help{color:#63796e;font-size:.9rem;line-height:1.55}
.v16-order-note{font-size:.85rem;color:#587265;overflow-wrap:anywhere}
.v16-pill{display:inline-block;padding:3px 9px;border-radius:99px;background:#e8f2e7;font-weight:700;margin-left:8px}
@media(max-width:700px){.v16-dispatch-grid{grid-template-columns:1fr}}
</style>
@endpush
@section('portalContent')
@if(($order->discount_pence??0)>0)<p class="portal-card"><strong>Promotion:</strong> −£{{number_format($order->discount_pence/100,2)}} ex VAT, included in totals.</p>@endif
<div class="portal-heading"><div><span class="portal-eyebrow">FULFILMENT MANAGEMENT</span><h1>{{$order->order_number}}</h1><p>{{$order->business_name}} · {{$order->created_at->format('d M Y H:i')}}</p></div><div><a class="portal-minibtn" href="{{route('admin.orders.invoice',$order)}}" target="_blank">Invoice / statement ↗</a> <a class="portal-minibtn" href="{{route('admin.orders.index')}}">← All orders</a></div></div>
<div class="portal-two">
<section class="portal-card"><h2>Order items</h2><div class="portal-tablewrap"><table class="portal-table"><thead><tr><th>Product</th><th>Cases</th><th>Amount</th></tr></thead><tbody>@foreach($order->items as $item)<tr><td><strong>{{$item->product_name}}</strong><small>{{$item->sku}} · {{$item->variant_label}}</small></td><td>{{$item->packs}}</td><td>£{{number_format($item->line_subtotal_pence/100,2)}}</td></tr>@endforeach</tbody></table></div></section>
<section class="portal-card"><h2>Payment &amp; fulfilment</h2>
<div class="portal-totals"><div><span>Payment</span><strong>{{ucfirst($order->payment_status)}} @if($order->payment?->provider)<span class="v16-pill">{{strtolower($order->payment->provider)==='paypal'?'PayPal':ucfirst($order->payment->provider)}}</span>@endif</strong></div><div><span>Fulfilment</span><strong>{{ucfirst($order->status)}}</strong></div><div><span>Order total</span><strong>£{{number_format($order->grand_total_pence/100,2)}}</strong></div></div>
@if($order->payment_status==='paid' && !in_array($order->status,['cancelled','delivered'],true))
<form method="post" action="{{route('admin.orders.status',$order)}}" class="portal-form" id="v16-status-form">@csrf
<label>Update fulfilment status
<select name="status" id="v16-status" required>
@foreach(['processing','dispatched','delivered'] as $st)
@if(!($order->status==='dispatched' && $st==='processing'))<option value="{{$st}}" @selected(old('status',$order->status)===$st)>{{ucfirst($st)}}</option>@endif
@endforeach
</select></label>
<div class="v16-dispatch-panel" id="v16-dispatch-fields" hidden>
<h3 style="margin:0 0 7px">Dispatch tracking <span class="v16-order-note">(optional)</span></h3>
<p class="v16-dispatch-help">Add courier and tracking reference if available. If left empty, the customer receives a dispatch confirmation email without tracking details.</p>
<div class="v16-dispatch-grid"><label>Courier / carrier<input name="carrier" maxlength="120" autocomplete="off" value="{{old('carrier',$shipment?->carrier)}}" placeholder="DPD / Royal Mail / Palletways"></label><label>Tracking number<input name="tracking_number" maxlength="180" autocomplete="off" value="{{old('tracking_number',$shipment?->tracking_number)}}" placeholder="Optional reference"></label></div>
</div>
<button class="btn" type="submit">Save order status</button>
</form>
@else<p>Fulfilment changes are restricted for this order's payment or delivery status.</p>@endif
@if($order->dispatch_email_sent_at)
<p class="v16-order-note">Dispatch confirmation emailed on {{$order->dispatch_email_sent_at->format('d M Y H:i')}}.</p>
@elseif($order->status==='dispatched')
<p class="v16-order-note">Dispatch email status: {{ $order->dispatch_email_status ?? 'not recorded' }}.</p>
@if($order->payment_status==='paid')<form method="post" action="{{route('admin.orders.dispatch-email.retry',$order)}}">@csrf<button type="submit" class="portal-minibtn">Retry dispatch email</button></form>@endif
@endif
<h3>Delivery address</h3><p>{{implode(', ',array_filter((array)$order->shipping_address))}}</p>
<p class="v16-order-note">Payment session: {{$order->payment?->provider_session_id ?? 'Not recorded'}}</p>
</section></div>
@if($shipment)<section class="portal-card portal-form-width"><h2>Saved dispatch information</h2><p><strong>Courier:</strong> {{$shipment->carrier ?: 'Not provided'}}<br><strong>Tracking reference:</strong> {{$shipment->tracking_number ?: 'Not provided'}}</p></section>@endif
<script>
(function(){const select=document.getElementById('v16-status');const panel=document.getElementById('v16-dispatch-fields');if(!select||!panel)return;function refresh(){panel.hidden=select.value!=='dispatched';}select.addEventListener('change',refresh);refresh();})();
</script>
@endsection
