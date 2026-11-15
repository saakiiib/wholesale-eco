@extends('layouts.app')
@section('meta_description', $product->meta_description ?: 'Wholesale Eco Products packaging range')
@section('title',($product->meta_title ?: $product->name).' — Wholesale Eco Products')
@section('content')
@php
$gallery=[];
if($product->image_path){$gallery[]=['src'=>(str_starts_with($product->image_path,'catalogue/')&&file_exists(public_path('images/'.$product->image_path))?asset('images/'.$product->image_path):asset('storage/'.$product->image_path)),'alt'=>$product->name];}
foreach($product->images as $photo){$gallery[]=['src'=>(str_starts_with($photo->path,'catalogue/')&&file_exists(public_path('images/'.$photo->path))?asset('images/'.$photo->path):asset('storage/'.$photo->path)),'alt'=>$photo->alt_text?:$product->name];}
$catIcon=match($product->category?->slug??''){'cups'=>'cup','bags'=>'bag','boxes'=>'box','napkins'=>'napkin','bowls'=>'bowl',default=>'box'};
$variantRows=$product->variants->sortBy('label')->values();
@endphp
<nav class="breadcrumbs" aria-label="Breadcrumb"><a href="{{route('home')}}">Home</a><span>›</span><a href="{{route('catalogue')}}">All products</a><span>›</span>@if($product->category)<a href="{{route('catalogue',['category'=>$product->category->slug])}}">{{$product->category->name}}</a><span>›</span>@endif<strong>{{$product->name}}</strong></nav>
<div class="kite-product-shell">
 <aside class="kite-sidebar" aria-label="Product categories">
  <div class="kite-side-title">Browse packaging <span>↗</span></div>
  @foreach($sidebarCategories as $category)
   <a href="{{route('catalogue',['category'=>$category->slug])}}" class="{{($product->category_id===$category->id)?'active':''}}"><span class="side-arrow">›</span>{{$category->name}}</a>
  @endforeach
  <div class="kite-side-help"><strong>Need a regular supply?</strong><p>Open a trade account to track orders and reorder essentials.</p><a href="{{route('register')}}">Create account →</a></div>
 </aside>
 <div class="kite-product-main">
  <header class="kite-pageheading"><div><span class="eyebrow">PRODUCT RANGE</span><h1>{{$product->name}}</h1><p>Compare product options, specifications and case prices in one place.</p></div><div class="kite-count">{{ $variantRows->count() }} purchasing {{ $variantRows->count()===1?'option':'options' }}</div></header>
  <section class="kite-overview" aria-label="Product gallery, description and filters">
   <div class="kite-gallery"><div class="kite-image-stage" id="product-main-image">
     @if(count($gallery))<img id="gallery-active-img" src="{{$gallery[0]['src']}}" alt="{{$gallery[0]['alt']}}">@else<div class="kite-product-art"><svg viewBox="0 0 100 100" role="img" aria-label="Illustration of {{$product->name}}"><use href="{{asset('design-preview/assets/products.svg')}}#{{$catIcon}}"/></svg></div>@endif
    </div>
    @if(count($gallery)>1)<div class="kite-photo-thumbs">@foreach($gallery as $i=>$photo)<button type="button" class="gallery-thumb {{$i===0?'is-selected':''}}" data-gallery-src="{{$photo['src']}}" data-gallery-alt="{{$photo['alt']}}" aria-label="Display image {{$i+1}}"><img src="{{$photo['src']}}" alt=""></button>@endforeach</div>@endif
    <div class="kite-gallery-caption">Product imagery &amp; details</div>
   </div>
   <div class="kite-product-info">
    <div class="kite-intro-title">About this product</div>
    <p class="kite-lead">{{$product->description ?: 'Wholesale packaging for restaurants, takeaways and cafés. Compare variants below for available options, pack sizes and bulk savings.'}}</p>
    <ul class="kite-selling-points"><li>Business-focused case ordering with clear quantities</li><li>Compare available sizes and purchasing options</li><li>Automatic tier pricing where configured</li><li>Secure basket and delivery checkout</li></ul>
    <div class="kite-overview-meta"><span><strong>Category</strong> {{$product->category?->name??'Packaging'}}</span><span><strong>Pricing</strong> GBP, excluding VAT</span><span><strong>Available SKUs</strong> {{$variantRows->count()}}</span></div>
    <div class="kite-filter-panel"><div class="kite-filter-head"><div><strong>Find the right option</strong><small>Filter the variants below</small></div><button type="button" id="kite-clear-filters">Reset filters ↺</button></div>
     <label class="kite-search-label" for="kite-variant-search">Search size, colour, description or SKU</label><input id="kite-variant-search" class="kite-search" type="search" placeholder="e.g. brown, medium, 12oz, SKU…" autocomplete="off">
     <div class="kite-filter-note">Use the table below to compare wholesale prices and select a case quantity.</div>
    </div>
   </div>
  </section>
  <div class="kite-tabbar"><a class="active" href="#kite-order-matrix">▦ &nbsp;Products &amp; prices</a><a href="#kite-product-details">ⓘ &nbsp;About this range</a><a href="#kite-related-info">↗ &nbsp;Ordering help</a></div>
  <section id="kite-order-matrix" class="kite-order-section" aria-labelledby="kite-table-title">
   <div class="kite-order-title"><div><span class="eyebrow">WHOLESALE PRICE MATRIX</span><h2 id="kite-table-title">Choose your packaging options</h2><p>Price tiers apply per SKU. Prices shown exclude VAT. Add any quantity of cases to your basket.</p></div><div class="kite-price-switch"><span>Price per:</span><div class="kite-switch" role="group" aria-label="Display prices by case or unit"><button type="button" class="active" data-price-mode="case" aria-pressed="true">Case</button><button type="button" data-price-mode="unit" aria-pressed="false">Unit</button></div></div></div>
   <div class="we-units-toolbar"><span>Units per:</span><div class="kite-switch" role="group" aria-label="Show unit quantities by pack or pallet"><button type="button" class="active" data-unit-mode="pack" aria-pressed="true">Pack</button><button type="button" data-unit-mode="pallet" aria-pressed="false">Pallet</button></div><small>Pallet counts appear only when configured for that SKU. Pallet orders use standard quantity-tier prices.</small></div>
  <div class="kite-matrix-scroller"><table class="kite-matrix"><thead><tr class="kite-matrix-super"><th rowspan="2" class="kite-desc-col">Product</th><th rowspan="2" class="v9-spec">Capacity</th><th rowspan="2" class="v9-spec">L × W × H</th><th rowspan="2" class="v9-spec">Type</th><th rowspan="2"><span class="we-units-col-title">Units per pack</span></th><th colspan="4">Wholesale prices · <span class="kite-price-unit-title">per case</span></th><th rowspan="2">Stock</th><th rowspan="2">Order quantity</th></tr><tr class="kite-matrix-sub"><th>1–3<br><small>cases</small></th><th>4–9<br><small>cases</small></th><th>10–19<br><small>cases</small></th><th>20+<br><small>cases</small></th></tr></thead><tbody>
    @forelse($variantRows as $v)
     @php
      $prices=[];
      foreach([1,4,10,20] as $quantity){
       $applicable=$v->priceTiers->where('min_packs','<=',$quantity)->sortByDesc('min_packs')->first();
       $prices[]=$applicable ? (int)$applicable->unit_price_pence : (int)$v->base_pack_price_pence;
      }
      $attr=$v->attributes??[];
      $labelInfo=is_array($attr)?collect($attr)->map(fn($value,$key)=>is_scalar($value)?$key.': '.$value:'')->filter()->implode(' · '):'';
     @endphp
     <tr class="kite-variant-row" data-packs-per-pallet="{{$v->packs_per_pallet?:0}}" data-allow-pallet="{{$v->allow_pallet_orders?1:0}}" data-variant-search="{{strtolower($v->label.' '.$v->sku.' '.$labelInfo)}}">
      <td class="kite-description-cell"><div class="kite-variant-flex"><div class="kite-variant-mini">@if(count($gallery))<img src="{{$gallery[0]['src']}}" alt="">@else<svg viewBox="0 0 100 100" aria-hidden="true"><use href="{{asset('design-preview/assets/products.svg')}}#{{$catIcon}}"/></svg>@endif</div><div><strong>{{$v->label}}</strong><small>SKU: {{$v->sku}}</small>@if($labelInfo)<small>{{$labelInfo}}</small>@endif</div></div></td>
      <td class="v9-spec">{{isset($attr['capacity_ml'])?number_format($attr['capacity_ml']).' ml':'—'}}</td><td class="v9-spec">{{collect([($attr['length_mm']??null),($attr['width_mm']??null),($attr['height_mm']??null)])->filter(fn($n)=>$n!==null)->implode(' × ')}} {{isset($attr['length_mm'])?'mm':''}}</td><td class="v9-spec">{{$attr['type']??$attr['style']??$attr['material']??'—'}}</td><td class="kite-pack-count"><strong class="we-unit-pack">{{number_format($v->units_per_pack)}}</strong><strong class="we-unit-pallet" hidden>@if($v->show_pallet_units && $v->packs_per_pallet){{number_format($v->units_per_pack*$v->packs_per_pallet)}}@else—@endif</strong><small class="we-unit-pack">units / pack</small><small class="we-unit-pallet" hidden>@if($v->show_pallet_units && $v->packs_per_pallet)units / pallet ({{$v->packs_per_pallet}} packs)@else Not configured @endif</small></td>
      @foreach($prices as $index=>$price)<td class="kite-price-cell" data-tier-index="{{$index}}"><strong class="kite-tier-price" data-price-pence="{{$price}}" data-units="{{$v->units_per_pack}}" data-unit-enabled="{{$v->show_unit_prices?1:0}}">£{{number_format($price/100,2)}}</strong>@if($index>0 && $price<$prices[0])<small class="kite-save-pill">Save {{round((1-$price/max(1,$prices[0]))*100)}}%</small>@endif</td>@endforeach
      <td><span class="stock-label {{$v->stock_packs>0?'in-stock':'out-stock'}}">{{$v->stock_packs>0?'In stock':'Out of stock'}}</span>@if($v->stock_packs>0)<small>{{$v->stock_packs}} cases</small>@endif</td>
      <td class="kite-order-cell"><form method="post" action="{{route('cart.add')}}" class="kite-quick-add">@csrf<input type="hidden" name="variant_id" value="{{$v->id}}"><input type="hidden" name="order_unit" value="pack">@if($v->allow_pallet_orders && $v->packs_per_pallet)<select class="we-order-unit" aria-label="Order in packs or pallets"><option value="pack">Packs</option><option value="pallet">Pallets</option></select>@endif<div class="kite-stepper"><button type="button" class="kite-step-down" aria-label="Decrease cases">−</button><input type="number" name="packs" value="1" min="1" max="10000" data-stock-packs="{{$v->stock_packs}}" aria-label="Cases of {{$v->label}}" inputmode="numeric"><button type="button" class="kite-step-up" aria-label="Increase cases">+</button></div><button class="kite-add-button" @disabled($v->stock_packs<=0)>Add <span aria-hidden="true">↗</span></button></form></td>
     </tr>
    @empty<tr><td colspan="11" class="kite-empty-table">No active product variants are available yet.</td></tr>@endforelse
    <tr id="kite-no-results" hidden><td colspan="11">No matching product options. <button type="button" id="kite-reset-inline">Clear the search</button>.</td></tr>
   </tbody></table></div>
   <div class="kite-table-foot"><span>✓ Secure business checkout</span><span>✓ Pack prices calculated at checkout</span><span>ⓘ Pallet ordering where configured; no special pallet or Mix &amp; Match discounts are included</span></div>
  </section>
  <section class="kite-bottom-cards" id="kite-product-details"><div><h3>Product information</h3><p>{{$product->description ?: 'Please contact our team if you need dimensions, material specifications or packaging suitability details.'}}</p></div><div id="kite-related-info"><h3>Need help ordering?</h3><p>Select a quantity of cases in the table and add it to the basket. Review VAT and delivery costs at checkout.</p><a href="{{route('cart.index')}}">View basket →</a></div><div><h3>Make restocking easier</h3><p>Create a trade account to follow your orders, save purchasing lists and quickly reorder supplies.</p><a href="{{route('register')}}">Create a trade account →</a></div></section>
 </div>
</div>
@push('styles')<link rel="stylesheet" href="{{asset('css/kite-product-v4.css')}}"><link rel="stylesheet" href="{{asset('css/v8-improvements.css')}}">@endpush
@push('scripts')<script src="{{asset('js/kite-product-v4.js').'?v=10'}}" defer></script>@endpush
@endsection
