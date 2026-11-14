<?php
namespace App\Http\Controllers;
use App\Models\Order;
use App\Models\SavedList;
use Illuminate\Http\Request;
class AccountController {
 private function businessId(Request $r):int{abort_if(!$r->user()->business_id,403);return (int)$r->user()->business_id;}
 public function dashboard(Request $r){$id=$this->businessId($r);return view('account.dashboard',['orders'=>Order::where('business_id',$id)->latest()->with('items')->paginate(10),'lists'=>SavedList::where('business_id',$id)->with('items.variant.product')->get()]);}
 public function reorder(Request $r,Order $order){abort_unless($order->business_id===$this->businessId($r),403);
  $cart=$r->session()->get('cart',[]);foreach($order->items as $item){if(!$item->product_variant_id)continue;$cart[$item->product_variant_id]=(int)($cart[$item->product_variant_id]??0)+$item->packs;}
  $r->session()->put('cart',$cart);return redirect()->route('cart.index')->with('success','Products added. Current stock and pricing will be checked at checkout.');
 }
 public function saveList(Request $r){$id=$this->businessId($r);$data=$r->validate(['name'=>'required|string|max:150']);$list=SavedList::create(['name'=>$data['name'],'business_id'=>$id]);
  foreach($r->session()->get('cart',[]) as $variantId=>$packs){$list->items()->create(['product_variant_id'=>$variantId,'packs'=>$packs]);}
  return back()->with('success','List saved');}
 public function loadList(Request $r,SavedList $list){abort_unless($list->business_id===$this->businessId($r),403);$cart=$r->session()->get('cart',[]);foreach($list->items as $item)$cart[$item->product_variant_id]=(int)($cart[$item->product_variant_id]??0)+$item->packs;
  $r->session()->put('cart',$cart);return redirect()->route('cart.index');}
}
