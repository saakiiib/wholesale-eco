@extends('layouts.app')
@push('styles')<link rel="stylesheet" href="{{asset('css/portal-v3.css')}}">@if($portal==='admin')<link rel="stylesheet" href="{{asset('css/admin-v6.css')}}">@endif @endpush
@section('content')
<div class="portal-shell">
 <aside class="portal-sidebar">
 <div class="portal-brand"><span class="portal-mark">W</span><div><strong>{{ $portal === 'admin' ? 'Wholesale Eco' : 'Trade account' }}</strong><small>WHOLESALE ECO PRODUCTS</small></div></div>
 @if($portal==='admin')
  <div class="portal-navlabel">WORKSPACE</div>
  <a class="{{request()->routeIs('admin.dashboard')?'active':''}}" href="{{route('admin.dashboard')}}">▦ &nbsp; Overview</a>
  <a class="{{request()->routeIs('admin.orders.*')?'active':''}}" href="{{route('admin.orders.index')}}">▤ &nbsp; Orders &amp; Fulfilment</a>
  <a class="{{request()->routeIs('admin.products.*')?'active':''}}" href="{{route('admin.products.index')}}">▣ &nbsp; Product Catalogue</a>
  <a class="{{request()->routeIs('admin.categories.*')?'active':''}}" href="{{route('admin.categories.index')}}">↳ &nbsp; Manage Categories</a>
  <a class="{{request()->routeIs('admin.variants','admin.stock','admin.tier')?'active':''}}" href="{{route('admin.variants')}}">▥ &nbsp; Inventory &amp; Stock</a>
  <a class="{{request()->routeIs('admin.variants','admin.tier')?'active':''}}" href="{{route('admin.variants')}}">£ &nbsp; Wholesale Pricing</a>
  <a class="{{request()->routeIs('admin.customers.*')?'active':''}}" href="{{route('admin.customers.index')}}">♙ &nbsp; Trade Customers</a>
  <a class="{{request()->routeIs('admin.reports')?'active':''}}" href="{{route('admin.reports')}}">▥ &nbsp; Analytics &amp; Reports</a>
  <div class="portal-navlabel">STORE MANAGEMENT</div>
  <a class="{{request()->routeIs('admin.settings.*')?'active':''}}" href="{{route('admin.settings.index')}}">⚙ &nbsp; Business Setup</a>
  <a class="{{request()->routeIs('admin.shipping.*')?'active':''}}" href="{{route('admin.shipping.index')}}">▤ &nbsp; Delivery Rules</a>
  <a class="{{request()->routeIs('admin.content.*')?'active':''}}" href="{{route('admin.content.index')}}">▧ &nbsp; Content Management</a>
  <a class="{{request()->routeIs('admin.seo.*')?'active':''}}" href="{{route('admin.seo.index')}}">⌕ &nbsp; SEO Management</a>
  <div class="portal-navlabel">MORE</div>
  <a class="{{request()->routeIs('admin.team.*')?'active':''}}" href="{{route('admin.team.index')}}">♧ &nbsp; Admin Users</a>
  @if(auth()->user()?->role==='admin')<a class="{{request()->routeIs('admin.roles.*')?'active':''}}" href="{{route('admin.roles.index')}}">🔐 &nbsp; Roles &amp; Permissions</a>@endif
  <a class="{{request()->routeIs('admin.audit.*')?'active':''}}" href="{{route('admin.audit.index')}}">◷ &nbsp; Activity History</a>
 @else
  <div class="portal-navlabel">YOUR WORKSPACE</div>
  <a class="{{request()->routeIs('account.dashboard')?'active':''}}" href="{{route('account.dashboard')}}">◈ &nbsp; Overview</a>
  <a class="{{request()->routeIs('account.orders.*')?'active':''}}" href="{{route('account.orders.index')}}">▤ &nbsp; My orders</a>
  <a class="{{request()->routeIs('account.lists.*')?'active':''}}" href="{{route('account.lists.index')}}">♡ &nbsp; Saved supply lists</a>
  <a class="{{request()->routeIs('account.addresses.*')?'active':''}}" href="{{route('account.addresses.index')}}">⌖ &nbsp; Delivery addresses</a>
  <a class="{{request()->routeIs('account.profile*')?'active':''}}" href="{{route('account.profile')}}">♙ &nbsp; Business profile</a>
 @endif
 <div class="portal-navlabel">QUICK LINKS</div>
 <a href="{{route('catalogue')}}">↗ &nbsp; Shop wholesale</a><a href="{{route('cart.index')}}">▱ &nbsp; View basket</a>
 <div class="portal-sidebar-foot">Secure {{ $portal==='admin'?'administrator':'trade customer' }} workspace</div>
 </aside>
 <div class="portal-workspace">
  <div class="portal-topbar"><span>{{ $portal==='admin'?'STORE OPERATIONS · Wholesale Eco Products':'Your business, all in one place' }}</span><div class="portal-userbadge">{{strtoupper(substr(auth()->user()->name,0,1))}} <span>{{auth()->user()->name}}</span></div></div>
  <div class="portal-page">@yield('portalContent')</div>
 </div>
</div>
@endsection
