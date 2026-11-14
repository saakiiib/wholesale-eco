<?php
namespace App\Http\Controllers;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
class ShopController {
 public function home(){return view('shop.home',['categories'=>Category::where('active',true)->orderBy('sort_order')->get(),'homepageSettings'=>\App\Models\StoreSetting::whereIn('key',['hero_label','hero_title','hero_description','promo_heading','promo_description'])->pluck('value','key'), 'featured'=>ProductVariant::with('product')->where('active',true)->whereHas('product',fn($q)=>$q->where('active',true))->take(6)->get()]);}
 public function catalogue(Request $request){
  $categories=Category::where('active',true)->orderBy('sort_order')->orderBy('name')->get();
  $category=null;
  $categoryIds=[];
  if($request->filled('category')){
   $category=Category::where('slug',$request->query('category'))->where('active',true)->firstOrFail();
   // Include all nested categories; protect against accidental cyclic parent links.
   $categoryIds=[$category->id];
   for($i=0;$i<count($categoryIds);$i++){
    foreach($categories->where('parent_id',$categoryIds[$i]) as $child){
     if(!in_array($child->id,$categoryIds,true)) $categoryIds[]=$child->id;
    }
   }
  }
  $products=Product::with(['variants'=>fn($q)=>$q->where('active',true)->with('priceTiers'),'category','images'])
   ->where('active',true)
   ->when($category,fn($q)=>$q->whereIn('category_id',$categoryIds))
   ->when($request->filled('q'),function($q)use($request){
    $term='%'.$request->query('q').'%';
    $q->where(fn($w)=>$w->where('name','like',$term)->orWhere('description','like',$term)->orWhereHas('variants',fn($v)=>$v->where('sku','like',$term)->orWhere('label','like',$term)));
   })->orderBy('name')->paginate($category?12:20)->withQueryString();
  $ancestors=collect();
  if($category){
   $current=$category;
   $seen=[$current->id];
   while($current->parent_id){
    $parent=$categories->firstWhere('id',$current->parent_id);
    if(!$parent || in_array($parent->id,$seen,true)) break;
    $ancestors->prepend($parent);$seen[]=$parent->id;$current=$parent;
   }
  }
  return view($category?'shop.category':'shop.catalogue',compact('products','categories','category','ancestors'));
 }
 public function product(Product $product){
  abort_unless($product->active,404);
  $product->load(['category','images','variants'=>fn($q)=>$q->where('active',true)->with('priceTiers')]);
  $sidebarCategories=Category::where('active',true)->orderBy('sort_order')->get();
  return view('shop.product',compact('product','sidebarCategories'));
 }
}
