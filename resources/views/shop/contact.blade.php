@extends('layouts.app')
@section('title','Contact Wholesale Eco Products')
@section('meta_description','Contact Wholesale Eco Products about wholesale packaging, products and trade accounts.')
@section('content')
<section class="we-contact-hero"><span class="eyebrow">WE'RE HERE TO HELP</span><h1>Let's talk packaging.</h1><p>Need help with case quantities, matching lids, repeat orders or opening a trade account? Contact our team.</p></section>
<div class="we-contact-grid"><section class="we-contact-card"><h2>Get in touch</h2><p>We welcome enquiries from restaurants, cafés, caterers and takeaway businesses.</p>
@if(filled($contact['business_phone']??null) && ($contact['business_phone']??'')!=='020 7946 0000')<div class="we-contact-detail"><small>Call us</small><a href="tel:{{preg_replace('/[^+0-9]/','',$contact['business_phone'])}}">{{$contact['business_phone']}}</a></div>@endif
@php $mail=$contact['support_email']??$contact['business_email']??null; @endphp
@if($mail && !str_ends_with($mail,'@example.test'))<div class="we-contact-detail"><small>Email us</small><a href="mailto:{{$mail}}">{{$mail}}</a></div>@endif
@if(filled($contact['business_address']??null) && !str_contains($contact['business_address'],'1 Example Street'))<div class="we-contact-detail"><small>Business address</small><p style="white-space:pre-line">{{$contact['business_address']}}</p></div>@endif
<a class="btn" href="{{route('register')}}">Open a trade account ↗</a></section>
<section class="we-contact-card"><h2>Send an enquiry</h2><form action="{{route('contact.send')}}" method="post" class="we-contact-form">@csrf
<div class="we-contact-fields"><label>Your name *<input required name="name" maxlength="120" value="{{old('name')}}"></label><label>Email address *<input required type="email" name="email" maxlength="180" value="{{old('email')}}"></label></div>
<label>Business name<input name="business" maxlength="180" value="{{old('business')}}"></label><label>How can we help? *<input required name="subject" maxlength="160" value="{{old('subject')}}"></label><label>Message *<textarea required minlength="10" maxlength="4000" rows="6" name="message">{{old('message')}}</textarea></label><div class="we-honeypot" aria-hidden="true"><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></div><button class="btn" type="submit">Send enquiry ↗</button><small>We use your details to respond to your enquiry.</small></form></section></div>
@push('styles')<link rel="stylesheet" href="{{asset('css/v8-improvements.css')}}">@endpush
@endsection
