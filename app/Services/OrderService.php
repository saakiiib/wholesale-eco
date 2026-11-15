<?php
namespace App\Services;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\Coupon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class OrderService {
 public function __construct(private PricingService $pricing){}
 public function createPending(array $cart,array $data,?int $businessId,?int $userId,?string $couponCode=null):Order{
  return DB::transaction(function()use($cart,$data,$businessId,$userId,$couponCode){
   // Lock SKU rows to eliminate oversell between validation and reservation.
   $ids=array_map('intval',array_keys($cart));sort($ids);
   ProductVariant::whereIn('id',$ids)->orderBy('id')->lockForUpdate()->get();
   // Serialize usage-limited coupon reservations across concurrent checkouts.
   $coupon=$couponCode?Coupon::where('code',strtoupper(trim($couponCode)))->lockForUpdate()->first():null;
   if($couponCode&&!$coupon)throw new \DomainException('Discount code not found.');
   $quote=$this->pricing->quote($cart,$businessId,$couponCode);
   if($coupon)$coupon->increment('reserved_count');
   if(!$quote['lines'])throw new \DomainException('Basket is empty.');
   $order=Order::create([
    'order_number'=>'WEP-'.strtoupper(Str::random(10)), 'checkout_token'=>Str::random(40),
    'business_id'=>$businessId,'user_id'=>$userId,'email'=>$data['email'],'business_name'=>$data['business_name'],
    'contact_name'=>$data['contact_name'],'phone'=>$data['phone']??null,
    'shipping_address'=>['line1'=>$data['line1'],'line2'=>$data['line2']??null,'city'=>$data['city'],'postcode'=>$data['postcode'],'country'=>'GB'],
    'subtotal_pence'=>$quote['subtotal_pence'],'coupon_id'=>$coupon?->id,'discount_pence'=>$quote['discount_pence'],'delivery_pence'=>$quote['delivery_pence'],'vat_pence'=>$quote['vat_pence'],'grand_total_pence'=>$quote['grand_total_pence'],
   ]);
   foreach($quote['lines'] as $line){$v=$line['variant'];
    $order->items()->create(['product_variant_id'=>$v->id,'sku'=>$v->sku,'product_name'=>$v->product->name,'variant_label'=>$v->label,
      'packs'=>$line['packs'],'units_per_pack'=>$v->units_per_pack,'unit_pack_price_pence'=>$line['price_pence'],'line_subtotal_pence'=>$line['subtotal_pence'],
      'line_vat_pence'=>$line['vat_pence'],'vat_rate_bps'=>$v->vat_rate_bps]);
    $v->decrement('stock_packs',$line['packs']);
    DB::table('stock_movements')->insert(['product_variant_id'=>$v->id,'order_id'=>$order->id,'delta_packs'=>-$line['packs'],'reason'=>'sale','created_at'=>now(),'updated_at'=>now()]);
   }
   return $order->load('items');
  },3);
 }
 public function releaseUnpaid(Order $order):void{
  DB::transaction(function()use($order){
   $order=Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
   if($order->payment_status!=='unpaid'||$order->status!=='pending_payment')return;
   if($order->coupon_id){$coupon=Coupon::whereKey($order->coupon_id)->lockForUpdate()->first();if($coupon&&$coupon->reserved_count>0)$coupon->decrement('reserved_count');}
   foreach($order->items as $item){if($item->product_variant_id){ProductVariant::whereKey($item->product_variant_id)->increment('stock_packs',$item->packs);
    DB::table('stock_movements')->insert(['product_variant_id'=>$item->product_variant_id,'order_id'=>$order->id,'delta_packs'=>$item->packs,'reason'=>'cancel','created_at'=>now(),'updated_at'=>now()]);}}
   $order->update(['status'=>'cancelled','payment_status'=>'failed']);
  });
 }
}
