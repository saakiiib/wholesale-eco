@php
 $company = $settings['business_name'] ?? 'Wholesale Eco Products';
 $support = $settings['support_email'] ?? $settings['business_email'] ?? null;
 $phone = $settings['business_phone'] ?? null;
 $address = $order->shipping_address ?? [];
@endphp
<!doctype html><html lang="en-GB"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Order dispatched</title></head>
<body style="margin:0;background:#f1f5f0;color:#1e392f;font:14px/1.6 Arial,Helvetica,sans-serif">
<div style="max-width:640px;padding:24px 14px;margin:auto">
 <header style="background:#123c33;color:#fff;padding:23px 27px;border-radius:10px 10px 0 0"><strong style="font-size:20px">{{ $company }}</strong><div style="font-size:12px;opacity:.85">Wholesale packaging supplies</div></header>
 <main style="background:#fff;border:1px solid #dce6dd;border-top:0;padding:27px">
  <h1 style="font-size:25px;color:#123c33;margin:0 0 14px">Your order has been dispatched</h1>
  <p>Hello {{ $order->contact_name ?: $order->business_name }},</p>
  <p>Good news — your Wholesale Eco Products order <strong>{{ $order->order_number }}</strong> has been dispatched.</p>
  @if($carrier || $trackingNumber)
   <div style="background:#eef5ee;border-radius:8px;padding:16px 20px;margin:18px 0">
    <strong>Delivery tracking</strong>
    @if($carrier)<div>Courier: {{ $carrier }}</div>@endif
    @if($trackingNumber)<div>Tracking reference: <strong>{{ $trackingNumber }}</strong></div>@endif
    <div style="font-size:12px;color:#62776c;margin-top:8px">Please use the reference with the named courier, or contact our team for delivery assistance.</div>
   </div>
  @else
   <p>We have not included a tracking reference for this shipment. Please contact our team if you need an update.</p>
  @endif
  <h2 style="font-size:16px;margin:22px 0 8px">Order summary</h2>
  <table style="width:100%;border-collapse:collapse"><thead><tr><th style="padding:9px;text-align:left;background:#e6f0e6">Product</th><th style="padding:9px;text-align:center;background:#e6f0e6">Cases</th></tr></thead>
  <tbody>@foreach($order->items as $item)<tr><td style="border-bottom:1px solid #e3ebe3;padding:9px">{{ $item->product_name }} <span style="color:#62776c">({{ $item->sku }})</span></td><td style="border-bottom:1px solid #e3ebe3;padding:9px;text-align:center">{{ $item->packs }}</td></tr>@endforeach</tbody></table>
  <p><strong>Order total:</strong> £{{ number_format($order->grand_total_pence/100,2) }}</p>
  <h2 style="font-size:16px;margin-bottom:6px">Delivering to</h2>
  <p>{{ $order->business_name }}<br>{{ $address['line1']??'' }}<br>@if(!empty($address['line2'])){{ $address['line2'] }}<br>@endif{{ $address['city']??'' }} {{ $address['postcode']??'' }}<br>{{ $address['country']??'GB' }}</p>
  <p style="color:#62776c">Questions about your delivery? @if($support)Email {{ $support }}@endif @if($phone)or call {{ $phone }}@endif.</p>
 </main>
 <footer style="color:#657970;text-align:center;font-size:12px;padding:14px">{{ $company }} @if(!empty($settings['business_address']))· {{ $settings['business_address'] }}@endif</footer>
</div></body></html>
