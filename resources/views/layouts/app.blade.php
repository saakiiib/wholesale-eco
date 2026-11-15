<!doctype html>
<html lang="en-GB"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="theme-color" content="#123c33"><meta name="description" content="@yield('meta_description','Wholesale Eco Products: packaging supplies for UK coffee shops, restaurants and takeaways.')"><title>@yield('title','Wholesale Eco Products')</title><link rel="stylesheet" href="{{ asset('css/shop.css') }}"><link rel="stylesheet" href="{{ asset('css/eco-upgrade.css') }}"><link rel="stylesheet" href="{{ asset('css/v8-improvements.css') }}"><link rel="stylesheet" href="{{asset('css/v9-admin-store.css')}}">@stack('styles')</head>
<body>
@unless(request()->is('admin*'))
<div class="topline">Wholesale essentials for growing businesses <span class="top-emphasis">· Foodservice packaging at bulk prices</span></div>
<header class="sitehead"><div class="wrap">
 <div class="nav nav-main"><a class="brand" href="{{ route('home') }}" aria-label="Wholesale Eco Products home"><img src="{{ asset('design-preview/assets/wholesale-eco-logo.png') }}" alt="Wholesale Eco Products"></a>
 <form class="header-search" role="search" method="get" action="{{ route('catalogue') }}"><label class="sr-only" for="global-search">Search products</label><svg aria-hidden="true" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><circle cx="10.8" cy="10.8" r="7"/><path d="m16 16 5 5"/></svg><input id="global-search" name="q" placeholder="Search cups, bags, boxes, packaging…" value="{{ request('q') }}"><button type="submit">Search</button></form>
 <nav class="header-actions" aria-label="Account and basket"><a href="{{ auth()->check() ? (auth()->user()->isAdmin()?route('admin.dashboard'):route('account.dashboard')) : route('login') }}">♙ <span>{{ auth()->check()?'My account':'Trade login' }}</span></a><a class="basket-link" href="{{ route('cart.index') }}">🛒 <span>Basket</span> <b class="cart-count">{{ array_sum(session('cart',[])) }}</b></a>@auth<form method="post" action="{{route('logout')}}">@csrf<button class="plain">Log out</button></form>@endauth</nav>
 </div>
 <nav class="category-nav" aria-label="Product categories"><a href="{{ route('catalogue') }}">All Products</a><a href="{{ route('catalogue',['category'=>'cups']) }}">Cups &amp; Lids</a><a href="{{ route('catalogue',['category'=>'boxes']) }}">Food Packaging</a><a href="{{ route('catalogue',['category'=>'bags']) }}">Bags &amp; Carriers</a><a href="{{ route('catalogue',['category'=>'napkins']) }}">Napkins &amp; Tableware</a><a href="{{ route('catalogue') }}">Eco Range</a><a class="special" href="{{ route('catalogue') }}">Bulk Deals ✦</a>@guest<a href="{{route('register')}}">Create Trade Account</a>@endguest</nav>
 </div></header>
@endunless
 <main class="{{ request()->is('admin*') ? 'admin-site-main' : 'wrap site-main' }}">
 @if(session('success'))<div class="alert success" role="status">{{ session('success') }}</div>@endif
 @if($errors->any())<div class="alert error" role="alert">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
 @yield('content')</main>
 @unless(request()->is('admin*'))
 @php
  $footerSettings=\App\Models\StoreSetting::whereIn('key',['business_name','business_email','business_phone','business_address','support_email','facebook_url','instagram_url','linkedin_url','whatsapp_number'])->pluck('value','key');
  $footerEmail=$footerSettings['support_email']??$footerSettings['business_email']??null;
  $footerPhone=$footerSettings['business_phone']??null;
 @endphp
 <footer class="premium-footer"><div class="wrap"><div class="footer-grid">
 <div class="footer-about"><img src="{{ asset('design-preview/assets/wholesale-eco-logo.png') }}" alt="Wholesale Eco Products"><p>Packaging essentials for the businesses that keep Britain moving. Straightforward wholesale ordering, transparent case prices and easy reordering.</p><div class="footer-note">B2B wholesale supplies · UK</div><div class="v9-social-links">@foreach(['facebook_url'=>'Facebook','instagram_url'=>'Instagram','linkedin_url'=>'LinkedIn'] as $key=>$label)@if(filled($footerSettings[$key]??null))<a href="{{$footerSettings[$key]}}" target="_blank" rel="noopener noreferrer">{{$label}} ↗</a>@endif @endforeach</div>
 @if($footerPhone)<a href="tel:{{preg_replace('/[^+0-9]/','',$footerPhone)}}">☎ {{$footerPhone}}</a>@endif
 @if($footerEmail)<a href="mailto:{{$footerEmail}}">✉ {{$footerEmail}}</a>@endif
 @if(filled($footerSettings['business_address']??null))<p class="footer-contact-address">{{ $footerSettings['business_address'] }}</p>@endif</div>
 <div><h3>Shop</h3><a href="{{route('catalogue')}}">All packaging</a><a href="{{route('catalogue',['category'=>'cups'])}}">Cups &amp; lids</a><a href="{{route('catalogue',['category'=>'bags'])}}">Carrier bags</a><a href="{{route('catalogue',['category'=>'boxes'])}}">Food containers</a></div>
 <div><h3>Customer support</h3><a href="{{route('cart.index')}}">Basket</a><a href="{{route('checkout.form')}}">Checkout</a><a href="{{route('catalogue')}}">Product catalogue</a><a href="{{route('register')}}">Trade registration</a><a href="{{route('contact')}}">Contact us</a><a href="{{route('cookies')}}">Cookies & privacy choices</a></div>
 <div><h3>Trade accounts</h3><a href="{{route('register')}}">Create an account</a><a href="{{route('login')}}">Log in</a><a href="{{auth()->check()?(auth()->user()->isAdmin()?route('admin.dashboard'):route('account.dashboard')):route('login')}}">My dashboard</a><a href="{{route('catalogue')}}">Quick restock</a></div>
 </div>@if(filled($footerSettings['whatsapp_number']??null))@php $wa=preg_replace('/[^0-9]/','',$footerSettings['whatsapp_number']);@endphp<a class="v9-whatsapp-link" href="https://wa.me/{{$wa}}" target="_blank" rel="noopener noreferrer" aria-label="Chat with Wholesale Eco Products on WhatsApp">WhatsApp enquiries ↗</a>@endif
<div class="footer-bottom"><span>© {{ date('Y') }} Wholesale Eco Products. All rights reserved.</span><span>Design and Developed by <a href="https://mentosoftware.co.uk/" target="_blank" rel="noopener noreferrer">Mento Software</a></span><span>Prices shown excluding VAT unless otherwise indicated.</span></div></div></footer>
 @endunless
 @if(!request()->is('admin*'))
  @php $waFloat=\App\Models\StoreSetting::getValue('whatsapp_number');$waDigits=$waFloat?preg_replace('/[^0-9]/','',$waFloat):'';@endphp
  @if($waDigits)<a class="v9-whatsapp-float" href="https://wa.me/{{$waDigits}}" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp customer support">Chat via WhatsApp ↗</a>@endif
 @endif
 @if(session('basket_added'))
 <div class="basket-toast" role="status" aria-live="polite" data-basket-toast><div class="toast-check">✓</div><div class="toast-body"><strong>Added to your basket!</strong><span>{{session('basket_added.name')}} · {{session('basket_added.packs')}} {{(int)session('basket_added.packs')===1?'case':'cases'}}</span><a href="{{route('cart.index')}}">View basket →</a></div><button type="button" class="toast-close" aria-label="Close notification" data-close-toast>×</button></div>
 @endif
 <script src="{{asset('js/eco-upgrade.js')}}" defer></script><script src="{{asset('js/v9-consent.js')}}" defer></script>@stack('scripts')</body></html>
