<?php
namespace App\Http\Controllers;
use App\Models\Business;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PriceTier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class AdminController {
 public function dashboard(){return view('admin.dashboard',['orders'=>Order::latest()->paginate(15),'products'=>ProductVariant::with('product')->orderBy('stock_packs')->take(12)->get(),'sales'=>Order::where('payment_status','paid')->sum('grand_total_pence'),'customers'=>Business::count()]);}
 public function variants(){return view('portal.admin.inventory',['variants'=>ProductVariant::with('product','priceTiers')->orderBy('sku')->paginate(40)]);}
 public function stock(Request $request,ProductVariant $variant){$data=$request->validate(['adjustment'=>'required|integer|between:-100000,100000','note'=>'required|string|max:250']);
  DB::transaction(function()use($data,$request,$variant){$v=ProductVariant::whereKey($variant->id)->lockForUpdate()->firstOrFail();abort_if($v->stock_packs+$data['adjustment']<0,422,'Stock cannot be negative');$v->increment('stock_packs',$data['adjustment']);
   DB::table('stock_movements')->insert(['product_variant_id'=>$v->id,'delta_packs'=>$data['adjustment'],'reason'=>'adjustment','note'=>$data['note'],'created_at'=>now(),'updated_at'=>now()]);
   DB::table('audit_logs')->insert(['user_id'=>$request->user()->id,'action'=>'stock.adjust','subject_type'=>'product_variant','subject_id'=>$v->id,'changes'=>json_encode($data),'created_at'=>now(),'updated_at'=>now()]);
  });return back()->with('success','Stock updated');}
 public function tier(Request $request,ProductVariant $variant){$data=$request->validate(['min_packs'=>'required|integer|min:1|max:100000','unit_price_pence'=>'required|integer|min:1|max:100000000']);
  PriceTier::updateOrCreate(['product_variant_id'=>$variant->id,'min_packs'=>$data['min_packs']],['unit_price_pence'=>$data['unit_price_pence']]);
  return back()->with('success','Price tier saved');}
 public function status(Request $request,Order $order){$data=$request->validate(['status'=>'required|in:processing,dispatched,delivered']);abort_unless($order->payment_status==='paid',422,'Only paid orders can be fulfilled');$order->update(['status'=>$data['status']]);return back()->with('success','Order status updated');}
}
