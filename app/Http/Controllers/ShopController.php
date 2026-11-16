<?php
namespace App\Http\Controllers;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
class ShopController {
 public function home(){
  // Stored settings override these defaults; never index a missing key in the view.
  $homepageSettings=collect([
   'hero_label'=>'THE SMARTER WAY TO STOCK UP',
   'hero_title'=>'Packaging that works for your business.',
   'hero_description'=>'Premium everyday packaging at wholesale prices. Everything your café, takeaway or restaurant needs, ready to order in a few clicks.',
   'promo_heading'=>'Less time ordering. More time serving.',
   'promo_description'=>'From your morning coffee rush to the evening takeaway rush, restocking should be the easiest part of your day. Compare case sizes, see volume savings and order what you need.',
  ])->merge(\App\Models\StoreSetting::whereIn('key',['hero_label','hero_title','hero_description','promo_heading','promo_description'])->pluck('value','key')->filter(fn($v)=>filled($v)));
  return view('shop.home',['categories'=>Category::where('active',true)->orderBy('sort_order')->get(),'homepageSettings'=>$homepageSettings,'featured'=>ProductVariant::with('product')->where('active',true)->whereHas('product',fn($q)=>$q->where('active',true))->orderBy('product_id')->take(6)->get()]);
 }
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
   })->orderBy('sort_order')->orderBy('name')->paginate($category?12:20)->withQueryString();
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
  $sidebarCategories=Category::where('active',true)->orderBy('sort_order')->orderBy('name')->get();
  $sidebarCategoryAncestors=[];
  $currentCategory=$product->category;
  $visitedCategoryIds=[];
  while($currentCategory && $currentCategory->parent_id && !in_array($currentCategory->id,$visitedCategoryIds,true)){
   $visitedCategoryIds[]=$currentCategory->id;
   $parentCategory=$sidebarCategories->firstWhere('id',$currentCategory->parent_id);
   if(!$parentCategory)break;
   $sidebarCategoryAncestors[]=$parentCategory->id;
   $currentCategory=$parentCategory;
  }
  return view('shop.product',compact('product','sidebarCategories','sidebarCategoryAncestors'));
 }
}
