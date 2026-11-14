<!doctype html><html lang="en-GB"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>@yield('title','Wholesale Eco Products')</title><link rel="stylesheet" href="{{ asset('css/shop.css') }}"></head>
<body><div class="topline">Wholesale Eco Products · Bulk supply for UK foodservice businesses</div>
<header class="sitehead"><div class="wrap nav"><a class="brand" href="{{ route('home') }}"><img src="{{ asset('design-preview/assets/wholesale-eco-logo.png') }}" alt="Wholesale Eco Products"></a>
<nav><a href="{{ route('catalogue') }}">Shop wholesale</a><a href="{{ route('cart.index') }}">Basket ({{ count(session('cart',[])) }})</a>
@auth <a href="{{ auth()->user()->isAdmin()?route('admin.dashboard'):route('account.dashboard') }}">{{ auth()->user()->isAdmin()?'Admin':'My account' }}</a><form method="post" action="{{ route('logout') }}">@csrf<button class="plain">Log out</button></form>
@else <a href="{{ route('login') }}">Log in</a><a href="{{ route('register') }}">Trade account</a>@endauth</nav></div></header>
<main class="wrap">@if(session('success'))<div class="alert success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
@yield('content')</main><footer class="foot">Wholesale Eco Products · B2B packaging essentials · Prices shown excluding VAT unless stated</footer></body></html>
