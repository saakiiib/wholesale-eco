@extends('layouts.app')
@section('title','Shop bulk packaging — Wholesale Eco Products')
@section('content')<div class="pageintro"><span class="eyebrow">Bulk ordering made easy</span><h1>Wholesale product catalogue</h1><p>Select case quantities and compare volume prices at a glance.</p></div>
<div class="layout"><aside class="sidebar"><h3>Categories</h3><a href="{{route('catalogue')}}">All products</a>@foreach($categories as $c)<a href="{{route('catalogue',['category'=>$c->slug])}}">{{$c->name}}</a>@endforeach</aside>
<div class="mainpane"><form action="{{route('catalogue')}}" class="searchbar"><input name="q" value="{{request('q')}}" placeholder="Search product name or SKU"><button class="btn small">Search</button></form>
@forelse($products as $product)<section class="prod"><h2>{{$product->name}}</h2><p>{{$product->description}}</p><div class="table-scroll"><table><thead><tr><th>SKU / Variation</th><th>Units / case</th><th>1–3 cases</th><th>4–9</th><th>10–19</th><th>20+</th><th>Stock</th><th>Add cases</th></tr></thead><tbody>
@foreach($product->variants->where('active',true) as $v)
@php $prices=[];foreach([1,4,10,20] as $qty){$price=$v->base_pack_price_pence;foreach($v->priceTiers as $tier){if($qty >= $tier->min_packs){$price=$tier->unit_price_pence;break;}}$prices[]=$price;}@endphp
<tr><td><strong>{{$v->label}}</strong><small>{{$v->sku}}</small></td><td>{{$v->units_per_pack}}</td>@foreach($prices as $pr)<td>£{{number_format($pr/100,2)}}</td>@endforeach<td>{{$v->stock_packs}} cases</td>
<td><form method="post" action="{{route('cart.add')}}" class="qtyadd">@csrf<input type="hidden" name="variant_id" value="{{$v->id}}"><input type="number" name="packs" min="1" max="10000" value="1" aria-label="Cases for {{$v->sku}}"><button class="btn small" @disabled($v->stock_packs===0)>+ Add</button></form></td></tr>
@endforeach</tbody></table></div></section>@empty<p>No matching products.</p>@endforelse
<div class="paginate">{{$products->links()}}</div></div></div>@endsection
