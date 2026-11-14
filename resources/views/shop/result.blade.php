@extends('layouts.app')
@section('content')<div class="pageintro"><h1>{{ $isSuccess?'Thank you — payment verification pending':'Payment not completed' }}</h1><p>Order {{ $order->order_number }} · Current status: {{ $order->payment_status }}</p>
<p>{{ $isSuccess?'Stripe may still be confirming the payment. Check your business account before considering the order paid.':'You can return to the catalogue. If payment expired, stock will be released when the Stripe webhook is processed.' }}</p>
<a class="btn" href="{{route('home')}}">Back to the shop</a></div>@endsection
