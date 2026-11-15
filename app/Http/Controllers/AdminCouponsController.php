<?php
namespace App\Http\Controllers;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class AdminCouponsController {
 private function guard():void{abort_unless(auth()->user()?->role==='admin',403);}
 public function index(){ $this->guard();return view('portal.admin.coupons',['coupons'=>Coupon::orderByDesc('id')->paginate(30)]); }
 private function validated(Request $r,?Coupon $coupon=null):array {return $r->validate([
  'code'=>['required','string','regex:/^[A-Za-z0-9_-]{3,60}$/',Rule::unique('coupons','code')->ignore($coupon?->id)],
  'discount_type'=>'required|in:fixed,percent','amount'=>'required|numeric|gt:0|decimal:0,2|max:100000',
  'minimum_subtotal_pence'=>'required|integer|min:0|max:100000000','max_discount_pence'=>'nullable|integer|min:1|max:100000000',
  'max_uses'=>'nullable|integer|min:1|max:1000000','starts_at'=>'nullable|date','expires_at'=>'nullable|date|after:starts_at',
  'active'=>'sometimes|boolean']);}
 private function amount(array &$d):void{
  $n=(string)$d['amount'];unset($d['amount']);
  $parts=explode('.',$n,2);$minor=(int)$parts[0]*100+(int)str_pad($parts[1]??'',2,'0');
  abort_if($d['discount_type']==='percent'&&$minor>10000,422,'Percentage cannot exceed 100%.');
  $d['discount_value']=$minor;
 }
 public function store(Request $r){$this->guard();$d=$this->validated($r);$this->amount($d);$d['code']=strtoupper($d['code']);$d['active']=$r->boolean('active');if($d['discount_type']==='percent')abort_if($d['discount_value']>10000,422,'Percentage exceeds 100%.');Coupon::create($d);return back()->with('success','Discount code created.');}
 public function update(Request $r,Coupon $coupon){$this->guard();$d=$this->validated($r,$coupon);$this->amount($d);$d['code']=strtoupper($d['code']);$d['active']=$r->boolean('active');if($d['discount_type']==='percent')abort_if($d['discount_value']>10000,422,'Percentage exceeds 100%.');if($d['max_uses']!==null)abort_if($d['max_uses']<$coupon->redeemed_count+$coupon->reserved_count,422,'Usage cap cannot be lower than used or reserved.');$coupon->update($d);return back()->with('success','Discount code updated.');}
}
