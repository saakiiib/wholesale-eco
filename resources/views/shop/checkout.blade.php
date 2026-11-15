@extends('layouts.app')
@section('title','Secure checkout')
@section('content')<div class="pageintro"><h1>Business checkout</h1><p>Your order is securely processed by Stripe. No card numbers are stored here.</p></div>
<div class="twocol"><form class="panel formgrid" method="post" action="{{route('checkout.submit')}}">@csrf
<h2>Business & contact details</h2><label>Business name<input name="business_name" required value="{{old('business_name',auth()->user()?->business?->name)}}"></label>
<label>Contact name<input name="contact_name" required value="{{old('contact_name',auth()->user()?->name)}}"></label><label>Email<input type="email" name="email" required value="{{old('email',auth()->user()?->email)}}"></label>
<label>Phone<input name="phone" value="{{old('phone')}}"></label><h2>Delivery address</h2>
<label>Address line 1<input name="line1" required value="{{old('line1')}}"></label><label>Address line 2<input name="line2" value="{{old('line2')}}"></label>
<label>Town / City<input name="city" required value="{{old('city')}}"></label><label>Postcode<input name="postcode" required value="{{old('postcode')}}"></label>
<label class="check"><input type="checkbox" name="terms" value="1" required> I agree to the wholesale terms and confirm this is a business purchase.</label>
<button class="btn">Continue to secure payment →</button></form>
<aside class="panel summary"><h2>Order summary</h2>@foreach($quote['lines'] as $line)<p>{{$line['variant']->product->name}} ({{$line['packs']}} cases) <b>£{{number_format($line['subtotal_pence']/100,2)}}</b></p>@endforeach
<hr><p>Discount <b>−£{{number_format(($quote['discount_pence']??0)/100,2)}}</b></p><p>Products <b>£{{number_format($quote['subtotal_pence']/100,2)}}</b></p><p>Delivery <b>£{{number_format($quote['delivery_pence']/100,2)}}</b></p><p>VAT on products <b>£{{number_format($quote['vat_pence']/100,2)}}</b></p><h2>£{{number_format($quote['grand_total_pence']/100,2)}}</h2><small>Payment is final only after the verified Stripe webhook confirms it.</small></aside></div>
@endsection
