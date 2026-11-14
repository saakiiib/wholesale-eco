<?php
namespace App\Http\Controllers;
use App\Models\ProductVariant;
use App\Services\PricingService;
use Illuminate\Http\Request;
class CartController {
 public function index(Request $request,PricingService $pricing){
  $cart=$request->session()->get('cart',[]);$quote=null;$error=null;
  try{$quote=$pricing->quote($cart,$request->user()?->business_id);}catch(\DomainException $e){$error=$e->getMessage();}
  return view('shop.cart',compact('quote','error'));
 }
 public function add(Request $request){
  $data=$request->validate(['variant_id'=>'required|integer|exists:product_variants,id','packs'=>'required|integer|min:1|max:10000','order_unit'=>'sometimes|in:pack,pallet']);
  $variant=ProductVariant::findOrFail($data['variant_id']);abort_unless($variant->active,422);
  $pallet=($data['order_unit']??'pack')==='pallet';
  if($pallet){abort_unless($variant->allow_pallet_orders && $variant->packs_per_pallet,422);$data['packs']=(int)$data['packs']*(int)$variant->packs_per_pallet;}
  if($data['packs']>10000 || $data['packs']>$variant->stock_packs){return back()->withErrors(['packs'=>'Requested quantity exceeds available stock or the order limit.']);}
  $cart=$request->session()->get('cart',[]);$key=(string)$variant->id;
  $cart[$key]=min(10000,((int)($cart[$key]??0))+$data['packs']);$request->session()->put('cart',$cart);
  return redirect()->back()->with('basket_added', ['name'=>$variant->product->name, 'packs'=>(int)$data['packs']])->with('success','Product added to your basket.');
 }
 public function update(Request $request){
  $data=$request->validate(['variant_id'=>'required|integer|exists:product_variants,id','packs'=>'required|integer|min:0|max:10000']);
  $cart=$request->session()->get('cart',[]);$key=(string)$data['variant_id'];
  if($data['packs']===0)unset($cart[$key]);else $cart[$key]=$data['packs'];
  $request->session()->put('cart',$cart);return redirect()->route('cart.index');
 }
}
