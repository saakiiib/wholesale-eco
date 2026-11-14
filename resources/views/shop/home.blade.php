@extends('layouts.app')
@section('title','Wholesale Eco Products — Packaging for foodservice')
@section('content')
<section class="hero"><div><span class="eyebrow">Packaging for business</span><h1>Everything your business needs to pack, serve and deliver.</h1><p>Discover wholesale cups, food containers, bags, napkins and tableware. Clear case pricing, simple checkout and easy reordering.</p><a class="btn" href="{{route('catalogue')}}">Shop wholesale supplies →</a></div><div class="heroart">♻<span>FOODSERVICE<br>PACKAGING</span></div></section>
<h2>Shop by category</h2><div class="cards">@foreach($categories as $cat)<a class="tile" href="{{route('catalogue',['category'=>$cat->slug])}}"><span class="emoji">📦</span><strong>{{$cat->name}}</strong><small>Explore products →</small></a>@endforeach</div>
<h2>Popular packaging</h2><div class="cards">@forelse($featured as $v)<div class="tile"><small>{{$v->sku}}</small><strong>{{$v->product->name}}</strong><p>{{$v->label}}</p><span class="price">£{{number_format($v->base_pack_price_pence/100,2)}} / pack</span><form action="{{route('cart.add')}}" method="post">@csrf<input type="hidden" name="variant_id" value="{{$v->id}}"><input type="hidden" name="packs" value="1"><button class="btn small">Add to basket</button></form></div>@empty<p>Ask Sakib to run the demo seeders.</p>@endforelse</div>
@endsection
