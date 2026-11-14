<?php
namespace App\Http\Controllers;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
class ShopController {
 public function home(){return view('shop.home',['categories'=>Category::where('active',true)->orderBy('sort_order')->get(),'featured'=>ProductVariant::with('product')->where('active',true)->whereHas('product',fn($q)=>$q->where('active',true))->take(6)->get()]);}
 public function catalogue(Request $request){
  $products=Product::with(['variants.priceTiers','category'])->where('active',true)
  ->when($request->filled('category'),fn($q)=>$q->whereHas('category',fn($c)=>$c->where('slug',$request->query('category'))))
  ->when($request->filled('q'),fn($q)=>$q->where(function($w)use($request){$term='%'.$request->query('q').'%';$w->where('name','like',$term)->orWhereHas('variants',fn($v)=>$v->where('sku','like',$term));}))
  ->paginate(20)->withQueryString();
  return view('shop.catalogue',['products'=>$products,'categories'=>Category::where('active',true)->get()]);
 }
 public function product(Product $product){
  abort_unless($product->active,404);
  $product->load(['category','images','variants'=>fn($q)=>$q->where('active',true)->with('priceTiers')]);
  return view('shop.product',compact('product'));
 }
}
