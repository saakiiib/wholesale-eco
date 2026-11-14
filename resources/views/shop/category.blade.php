@extends('layouts.app')
@section('meta_description', $category->meta_description ?: 'Wholesale Eco Products packaging range')
@section('title', ($category->meta_title ?: $category->name).' — Wholesale Eco Products')
@section('content')
@php
$firstProduct=$products->first();
$gallery=[];
foreach($products as $p){
 if($p->image_path) $gallery[]=['src'=>asset('storage/'.$p->image_path),'alt'=>$p->name];
 foreach($p->images as $photo) $gallery[]=['src'=>asset('storage/'.$photo->path),'alt'=>$photo->alt_text?:$p->name];
 if(count($gallery)>=6) break;
}
$catIcon=match($category->slug){'cups'=>'cup','bags'=>'bag','boxes'=>'box','napkins'=>'napkin','bowls'=>'bowl',default=>'box'};
$rows=$products->flatMap(fn($p)=>$p->variants->map(fn($v)=>['product'=>$p,'variant'=>$v]));
$children=$categories->where('parent_id',$category->id);
@endphp
<nav class="breadcrumbs" aria-label="Breadcrumb"><a href="{{route('home')}}">Home</a><span>›</span><a href="{{route('catalogue')}}">Shop</a><span>›</span>@foreach($ancestors as $ancestor)<a href="{{route('catalogue',['category'=>$ancestor->slug])}}">{{$ancestor->name}}</a><span>›</span>@endforeach<strong>{{$category->name}}</strong></nav>
<div class="kite-product-shell">
 <aside class="kite-sidebar" aria-label="Product categories"><div class="kite-side-title">Browse packaging <span>↗</span></div>
 @foreach($categories as $navCategory)
  @php $isChild=(bool)$navCategory->parent_id; $selected=in_array($navCategory->id,[$category->id,...$ancestors->pluck('id')->all()],true); @endphp
  <a href="{{route('catalogue',['category'=>$navCategory->slug])}}" class="{{$selected?'active':''}} {{$isChild?'category-nested':''}}"><span class="side-arrow">›</span>{{$navCategory->name}}</a>
 @endforeach
 <div class="kite-side-help"><strong>Ordering for your business?</strong><p>Save repeat purchases, monitor deliveries and manage your account.</p><a href="{{route('register')}}">Open a trade account →</a></div></aside>
 <div class="kite-product-main">
  <header class="kite-pageheading"><div><span class="eyebrow">WHOLESALE CATEGORY</span><h1>{{$category->name}}</h1><p>Explore the range, compare pack sizes and order at volume prices.</p></div><div class="kite-count">{{$products->total()}} {{\Illuminate\Support\Str::plural('product',$products->total())}}</div></header>
  <section class="kite-overview" aria-label="Category overview, product images and search">
   <div class="kite-gallery"><div class="kite-image-stage" id="product-main-image">
    @if(count($gallery))<img id="gallery-active-img" src="{{$gallery[0]['src']}}" alt="{{$gallery[0]['alt']}}">
    @else<div class="kite-product-art"><svg viewBox="0 0 100 100" role="img" aria-label="{{$category->name}} illustration"><use href="{{asset('design-preview/assets/products.svg')}}#{{$catIcon}}"/></svg></div>@endif
   </div>
   @if(count($gallery)>1)<div class="kite-photo-thumbs">@foreach(array_slice($gallery,0,5) as $i=>$photo)<button type="button" class="gallery-thumb {{$i===0?'is-selected':''}}" data-gallery-src="{{$photo['src']}}" data-gallery-alt="{{$photo['alt']}}" aria-label="Show product image {{$i+1}}"><img src="{{$photo['src']}}" alt=""></button>@endforeach</div>@endif
   <div class="kite-gallery-caption">Explore packaging products in this range</div></div>
   <div class="kite-product-info"><div class="kite-intro-title">About {{$category->name}}</div><p class="kite-lead">Wholesale packaging for cafés, restaurants and takeaways. Compare specifications, case quantities and bulk prices for products in this range.</p>
   <ul class="kite-selling-points"><li>Clear pricing by case and individual unit</li><li>Compare different sizes, colours and pack formats</li><li>Automatic quantity discounts per SKU where configured</li><li>Secure business checkout and quick repeat ordering</li></ul>
   @if($children->isNotEmpty())<div class="category-children"><strong>Explore subcategories</strong><div class="category-chip-list">@foreach($children as $child)<a href="{{route('catalogue',['category'=>$child->slug])}}">{{$child->name}} ↗</a>@endforeach</div></div>@endif
   <div class="kite-filter-panel"><div class="kite-filter-head"><div><strong>Find the right product</strong><small>Search within this category</small></div><button type="button" id="kite-clear-filters">Reset filters ↺</button></div>
    <label class="kite-search-label" for="kite-variant-search">Filter the displayed product options</label><input id="kite-variant-search" class="kite-search" type="search" placeholder="Search size, colour, description or SKU…" autocomplete="off">
    <form action="{{route('catalogue')}}" method="get" class="category-db-search"><input type="hidden" name="category" value="{{$category->slug}}"><label class="kite-search-label" for="category-db-query">Search all products in this category</label><div class="category-db-search-row"><input id="category-db-query" type="search" name="q" value="{{request('q')}}" placeholder="Product name or SKU"><button type="submit">Search</button></div></form>
   </div></div>
  </section>
  <div class="kite-tabbar"><a class="active" href="#kite-order-matrix">▦ &nbsp;Products &amp; prices</a><a href="#category-guide">ⓘ &nbsp;About this category</a><a href="#category-help">↗ &nbsp;Ordering help</a></div>
  <section class="kite-order-section" id="kite-order-matrix"><div class="kite-order-title"><div><span class="eyebrow">WHOLESALE PRICE MATRIX</span><h2>Choose your packaging options</h2><p>Product ranges and variants in one table. Case prices exclude VAT; volume discounts apply per SKU.</p></div><div class="kite-price-switch"><span>Price per:</span><div class="kite-switch" role="group" aria-label="Price display"><button type="button" class="active" data-price-mode="case" aria-pressed="true">Case</button><button type="button" data-price-mode="unit" aria-pressed="false">Unit</button></div></div></div>
  <div class="we-units-toolbar"><span>Units per:</span><div class="kite-switch" role="group" aria-label="Show unit quantities by pack or pallet"><button type="button" class="active" data-unit-mode="pack" aria-pressed="true">Pack</button><button type="button" data-unit-mode="pallet" aria-pressed="false">Pallet</button></div><small>Pallet counts appear only when configured for that SKU. Pallet orders use standard quantity-tier prices.</small></div>
  <div class="kite-matrix-scroller"><table class="kite-matrix"><thead><tr class="kite-matrix-super"><th rowspan="2" class="kite-desc-col">Product / specification</th><th rowspan="2"><span class="we-units-col-title">Units per pack</span></th><th colspan="4">Wholesale prices · <span class="kite-price-unit-title">per case</span></th><th rowspan="2">Stock</th><th rowspan="2">Order quantity</th></tr><tr class="kite-matrix-sub"><th>1–3<br><small>cases</small></th><th>4–9<br><small>cases</small></th><th>10–19<br><small>cases</small></th><th>20+<br><small>cases</small></th></tr></thead><tbody>
   @forelse($rows as $row)
    @php
     $p=$row['product'];$v=$row['variant'];$prices=[];
     foreach([1,4,10,20] as $quantity){$tier=$v->priceTiers->where('min_packs','<=',$quantity)->sortByDesc('min_packs')->first();$prices[]=$tier?(int)$tier->unit_price_pence:(int)$v->base_pack_price_pence;}
     $attr=$v->attributes??[];$attrs=is_array($attr)?collect($attr)->map(fn($value,$key)=>is_scalar($value)?$key.': '.$value:'')->filter()->implode(' · '):'';
     $img=$p->image_path?asset('storage/'.$p->image_path):($p->images->first()?asset('storage/'.$p->images->first()->path):null);
    @endphp
    <tr class="kite-variant-row" data-packs-per-pallet="{{$v->packs_per_pallet?:0}}" data-allow-pallet="{{$v->allow_pallet_orders?1:0}}" data-variant-search="{{strtolower($p->name.' '.$p->description.' '.$v->label.' '.$v->sku.' '.$attrs)}}"><td class="kite-description-cell"><div class="kite-variant-flex"><div class="kite-variant-mini">@if($img)<img src="{{$img}}" alt="">@else<svg viewBox="0 0 100 100" aria-hidden="true"><use href="{{asset('design-preview/assets/products.svg')}}#{{$catIcon}}"/></svg>@endif</div><div><strong><a class="category-product-link" href="{{route('products.show',$p)}}">{{$p->name}}</a></strong><small>{{$v->label}} · SKU: {{$v->sku}}</small>@if($attrs)<small>{{$attrs}}</small>@endif<a class="category-details-link" href="{{route('products.show',$p)}}">Images &amp; product information ↗</a></div></div></td>
    <td class="kite-pack-count"><strong class="we-unit-pack">{{number_format($v->units_per_pack)}}</strong><strong class="we-unit-pallet" hidden>@if($v->show_pallet_units && $v->packs_per_pallet){{number_format($v->units_per_pack*$v->packs_per_pallet)}}@else—@endif</strong><small class="we-unit-pack">units / pack</small><small class="we-unit-pallet" hidden>@if($v->show_pallet_units && $v->packs_per_pallet)units / pallet ({{$v->packs_per_pallet}} packs)@else Not configured @endif</small></td>
    @foreach($prices as $i=>$price)<td class="kite-price-cell" data-tier-index="{{$i}}"><strong class="kite-tier-price" data-price-pence="{{$price}}" data-units="{{$v->units_per_pack}}" data-unit-enabled="{{$v->show_unit_prices?1:0}}">£{{number_format($price/100,2)}}</strong>@if($i>0&&$price<$prices[0])<small class="kite-save-pill">Save {{round((1-$price/max(1,$prices[0]))*100)}}%</small>@endif</td>@endforeach
    <td><span class="stock-label {{$v->stock_packs>0?'in-stock':'out-stock'}}">{{$v->stock_packs>0?'In stock':'Out of stock'}}</span>@if($v->stock_packs>0)<small>{{$v->stock_packs}} cases</small>@endif</td>
    <td class="kite-order-cell"><form method="post" action="{{route('cart.add')}}" class="kite-quick-add">@csrf<input type="hidden" name="variant_id" value="{{$v->id}}"><input type="hidden" name="order_unit" value="pack">@if($v->allow_pallet_orders && $v->packs_per_pallet)<select class="we-order-unit" aria-label="Order in packs or pallets"><option value="pack">Packs</option><option value="pallet">Pallets</option></select>@endif<div class="kite-stepper"><button type="button" class="kite-step-down" aria-label="Decrease cases">−</button><input name="packs" type="number" value="1" min="1" max="{{max(1,min(10000,$v->stock_packs))}}" inputmode="numeric" aria-label="Cases of {{$p->name}} - {{$v->label}}"><button type="button" class="kite-step-up" aria-label="Increase cases">+</button></div><button class="kite-add-button" @disabled($v->stock_packs<=0)>Add ↗</button></form></td></tr>
   @empty<tr><td colspan="8" class="kite-empty-table">No matching products are currently available.</td></tr>@endforelse
   <tr id="kite-no-results" hidden><td colspan="8">No matching options on this page. <button type="button" id="kite-reset-inline">Clear the search</button>.</td></tr>
  </tbody></table></div>
  <div class="kite-table-foot"><span>✓ Secure business checkout</span><span>✓ Case and unit price comparison</span><span>ⓘ Pallet orders use the configured pack tiers; Mix &amp; Match pricing is not yet enabled</span></div>
  <div class="paginate">{{$products->links()}}</div></section>
  <section class="kite-bottom-cards" id="category-guide"><div><h3>More about this range</h3><p>Choose a product to view photos and full details, or select your case quantities directly above.</p></div><div id="category-help"><h3>Need help ordering?</h3><p>Quantity discounts are calculated per SKU at checkout. Delivery and VAT are calculated separately.</p><a href="{{route('cart.index')}}">View basket →</a></div><div><h3>Restock in seconds</h3><p>Save your regular products and repeat orders through a trade account.</p><a href="{{route('register')}}">Open a trade account →</a></div></section>
 </div>
</div>
@push('styles')<link rel="stylesheet" href="{{asset('css/kite-product-v4.css')}}"><link rel="stylesheet" href="{{asset('css/v8-improvements.css')}}"><link rel="stylesheet" href="{{asset('css/category-v5.css')}}">@endpush
@push('scripts')<script src="{{asset('js/kite-product-v4.js')}}" defer></script>@endpush
@endsection
