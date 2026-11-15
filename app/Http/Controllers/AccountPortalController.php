<?php
namespace App\Http\Controllers;
use App\Models\{Address,Business,Order,SavedList,ProductVariant};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class AccountPortalController {
 private function company(Request $r):Business {return Business::findOrFail($r->user()->business_id);}
 private function ownedOrder(Request $r,Order $order):Order {abort_unless($order->business_id === $this->company($r)->id,403);return $order;}
 public function dashboard(Request $r){$b=$this->company($r);return view('portal.customer.dashboard',[
   'business'=>$b,'recent'=>Order::where('business_id',$b->id)->latest()->take(6)->get(),
   'orderCount'=>Order::where('business_id',$b->id)->count(),
   'spend'=>Order::where('business_id',$b->id)->where('payment_status','paid')->sum('grand_total_pence'),
   'lists'=>SavedList::where('business_id',$b->id)->withCount('items')->latest()->take(4)->get()]);}
 public function orders(Request $r){$b=$this->company($r);$q=Order::where('business_id',$b->id)->latest();if(in_array($r->query('status'),['paid','processing','dispatched','delivered','cancelled'],true))$q->where('status',$r->query('status'));return view('portal.customer.orders',['orders'=>$q->paginate(15)->withQueryString()]);}
 public function order(Request $r,Order $order){$this->ownedOrder($r,$order);return view('portal.customer.order',['order'=>$order->load('items','payment')]);}
 public function orderPrint(Request $r,Order $order){$this->ownedOrder($r,$order);return view('portal.customer.order-print',['order'=>$order->load('items')]);}
 public function lists(Request $r){return view('portal.customer.lists',['lists'=>SavedList::where('business_id',$this->company($r)->id)->with('items.variant.product')->latest()->get()]);}
 public function deleteList(Request $r,SavedList $list){abort_unless($list->business_id===$this->company($r)->id,403);$list->delete();return back()->with('success','Saved list removed.');}
 public function addresses(Request $r){return view('portal.customer.addresses',['addresses'=>Address::where('business_id',$this->company($r)->id)->orderByDesc('is_default')->latest()->get()]);}
 public function storeAddress(Request $r){$b=$this->company($r);$data=$r->validate(['label'=>'required|string|max:60','contact_name'=>'nullable|string|max:150','line1'=>'required|string|max:200','line2'=>'nullable|string|max:200','city'=>'required|string|max:100','county'=>'nullable|string|max:100','postcode'=>'required|string|max:16','is_default'=>'sometimes|boolean']);
  DB::transaction(function()use($b,$data){$data['is_default']=!empty($data['is_default'])||!Address::where('business_id',$b->id)->exists();if($data['is_default'])Address::where('business_id',$b->id)->update(['is_default'=>false]);$b->addresses()->create($data);});return back()->with('success','Address saved.');}
 public function deleteAddress(Request $r,Address $address){$b=$this->company($r);abort_unless($address->business_id===$b->id,403);DB::transaction(function()use($address,$b){$wasDefault=$address->is_default;$address->delete();if($wasDefault){$next=Address::where('business_id',$b->id)->orderBy('id')->first();$next?->update(['is_default'=>true]);}});return back()->with('success','Address removed.');}
 public function profile(Request $r){return view('portal.customer.profile',['business'=>$this->company($r)]);}
 public function updateProfile(Request $r){$data=$r->validate(['name'=>'required|string|max:150','business_name'=>'required|string|max:200','trading_name'=>'nullable|string|max:200','phone'=>'nullable|string|max:40','vat_number'=>'nullable|string|max:40']);$b=$this->company($r);DB::transaction(function()use($r,$b,$data){$r->user()->update(['name'=>$data['name']]);$b->update(['name'=>$data['business_name'],'trading_name'=>$data['trading_name']??null,'phone'=>$data['phone']??null,'vat_number'=>$data['vat_number']??null]);});return back()->with('success','Trade profile updated.');}
 public function invoice(Request $r,Order $order){
  $this->ownedOrder($r,$order);
  return view('portal.invoice',['order'=>$order->load('items'),'settings'=>\App\Models\StoreSetting::pluck('value','key')]);
 }
}
