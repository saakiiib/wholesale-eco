@extends('portal.shell',['portal'=>'admin'])
@section('title','Product images')
@section('portalContent')
@include('portal.admin._head',['eyebrow'=>'PRODUCT CATALOGUE','heading'=>'Product image gallery','subtitle'=>$product->name,'back'=>route('admin.products.edit',$product)])
<div class="portal-two"><section class="portal-card"><h2>Images</h2><div class="admin-image-grid">@forelse($product->images as $image)<div class="admin-image"><img src="{{asset('storage/'.$image->path)}}" alt="{{$image->alt_text}}"><p>{{$image->alt_text}}</p><form method="post" action="{{route('admin.images.destroy',[$product,$image])}}">@csrf @method('DELETE')<button class="portal-minibtn">Remove</button></form></div>@empty<p>No images uploaded. Add the first one here.</p>@endforelse</div></section>
<section class="portal-card"><h2>Upload photo</h2><p>JPEG, PNG or WebP · max 4 MB. Use descriptive alt text for accessibility and product SEO.</p><form class="portal-form" method="post" enctype="multipart/form-data" action="{{route('admin.images.store',$product)}}">@csrf<label>Image file<input type="file" name="image" accept="image/jpeg,image/png,image/webp" required></label><label>Alternative text<input name="alt_text" maxlength="180" placeholder="Brown paper carrier bag side view"></label><button class="btn">Upload image</button></form></section></div>
@endsection
