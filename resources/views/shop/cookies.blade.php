@extends('layouts.app')
@section('title','Cookies and privacy choices — Wholesale Eco Products')
@section('content')
<section class="v9-policy"><span class="eyebrow">YOUR PRIVACY MATTERS</span><h1>Cookies & privacy choices</h1><p>We use necessary cookies to keep the basket, login sessions and security measures working. Optional analytics and marketing technologies are not enabled unless you choose to accept them and the integrations have been configured.</p>
<h2>Manage your preferences</h2><div class="v9-choice"><div><strong>Strictly necessary</strong><p>Cart sessions, authentication and security. These are required for the service.</p></div><strong>Always on</strong></div>
<div class="v9-choice"><div><strong>Analytics</strong><p>Helps us understand website performance, if an analytics integration is configured.</p></div><label><input type="checkbox" id="cookie-analytics"> Allow analytics</label></div>
<div class="v9-choice"><div><strong>Marketing</strong><p>Personalised advertising and conversions, only when the relevant services are configured.</p></div><label><input type="checkbox" id="cookie-marketing"> Allow marketing</label></div>
<button class="btn" type="button" id="cookie-save">Save my choices</button>
<h2>How to change consent</h2><p>Return to this page at any time to change your selection. You may also delete stored data through your browser settings. The operator must document any live third-party cookies and their retention periods before launch.</p>
<h2>Questions</h2><p>Contact us through our <a href="{{route('contact')}}">contact page</a> to request details about personal data used by the wholesale store.</p>
</section>
@push('scripts')<script src="{{asset('js/v9-consent.js')}}" defer></script>@endpush
@endsection
