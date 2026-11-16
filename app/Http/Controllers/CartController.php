<?php
namespace App\Http\Controllers;
use App\Models\ProductVariant;
use App\Services\PricingService;
use Illuminate\Http\Request;
class CartController {
 public function index(Request $request,PricingService $pricing){
  $cart=$request->session()->get('cart',[]);$quote=null;$error=null;
  try{$quote=$pricing->quote($cart,$request->user()?->business_id,$request->session()->get('coupon_code'));}catch(\DomainException $e){
   $error=$e->getMessage();
   if($request->session()->has('coupon_code')){
    $request->session()->forget('coupon_code');
    try{$quote=$pricing->quote($cart,$request->user()?->business_id);$error='The previous discount code is no longer applicable and was removed. '.$error;}
    catch(\DomainException $inner){$error=$inner->getMessage();}
   }
  }
  return view('shop.cart',compact('quote','error'));
 }
 public function add(Request $request){
  $data=$request->validate(['variant_id'=>'required|integer|exists:product_variants,id','packs'=>'required|integer|min:1|max:10000','order_unit'=>'sometimes|in:pack,pallet']);
  $variant=ProductVariant::with('product')->findOrFail($data['variant_id']);
  abort_unless($variant->active && $variant->product?->active,422,'This product is unavailable.');
  $pallet=($data['order_unit']??'pack')==='pallet';
  if($pallet){abort_unless($variant->allow_pallet_orders && $variant->packs_per_pallet,422);$data['packs']=(int)$data['packs']*(int)$variant->packs_per_pallet;}
  if($data['packs']<1 || $data['packs']>10000){return back()->withErrors(['packs'=>'Invalid case quantity.']);}
  $cart=$request->session()->get('cart',[]);$key=(string)$variant->id;
  $existing=(int)($cart[$key]??0);
  $requested=$existing+$data['packs'];
  if($requested>10000 || $requested>(int)$variant->stock_packs){return back()->withErrors(['packs'=>'Only '.max(0,(int)$variant->stock_packs-$existing).' additional cases are available for '.$variant->sku.'.']);}
  $cart[$key]=$requested;$request->session()->put('cart',$cart);
  return redirect()->back()->with('basket_added', ['name'=>$variant->product->name, 'packs'=>(int)$data['packs']])->with('success','Product added to your basket.');
 }
 public function coupon(Request $request,PricingService $pricing){
  $data=$request->validate(['code'=>'nullable|string|max:60']);
  $code=strtoupper(trim($data['code']??''));
  if($code===''){$request->session()->forget('coupon_code');return back()->with('success','Discount code removed.');}
  try{$pricing->quote($request->session()->get('cart',[]),$request->user()?->business_id,$code);}
  catch(\DomainException $e){return back()->withErrors(['coupon'=>$e->getMessage()]);}
  $request->session()->put('coupon_code',$code);return back()->with('success','Discount code applied.');
 }
 public function update(Request $request){
  $data=$request->validate(['variant_id'=>'required|integer|exists:product_variants,id','packs'=>'required|integer|min:0|max:10000']);
  $cart=$request->session()->get('cart',[]);$key=(string)$data['variant_id'];
  if($data['packs']===0)unset($cart[$key]);else {
   $variant=ProductVariant::with('product')->findOrFail($data['variant_id']);
   if(!$variant->active || !$variant->product?->active || $data['packs']>(int)$variant->stock_packs){
    return back()->withErrors(['packs'=>'The requested quantity is not available. Please reduce the number of cases.']);
   }
   $cart[$key]=$data['packs'];
  }
  $request->session()->put('cart',$cart);return redirect()->route('cart.index');
 }
}
