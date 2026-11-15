<?php
namespace App\Services;
use App\Models\ProductVariant;
use App\Models\Coupon;
use Illuminate\Support\Facades\DB;
class PricingService {
 /** @return array{lines:array,subtotal_pence:int,delivery_pence:int,vat_pence:int,grand_total_pence:int} */
 public function quote(array $cart,?int $businessId=null,?string $couponCode=null):array {
  $lines=[];$subtotal=0;$vat=0;
  foreach($cart as $variantId=>$packs){
   $packs=(int)$packs;if($packs<1)continue;
   $variant=ProductVariant::with(['product','priceTiers'])->findOrFail((int)$variantId);
   if(!$variant->active||!$variant->product->active)throw new \DomainException('An item in your basket is unavailable.');
   if($packs>$variant->stock_packs)throw new \DomainException('Insufficient stock for '.$variant->sku.'.');
   $price=(int)$variant->base_pack_price_pence;
   foreach($variant->priceTiers as $tier){if($packs >= $tier->min_packs){$price=$tier->unit_price_pence;break;}}
   if($businessId){$contract=DB::table('customer_prices')->where('business_id',$businessId)->where('product_variant_id',$variant->id)->first();if($contract)$price=(int)$contract->pack_price_pence;}
   $lineSubtotal=$price*$packs;
   // VAT rounded per line, using integer arithmetic.
   $lineVat=intdiv($lineSubtotal*(int)$variant->vat_rate_bps+5000,10000);
   $lines[]=['variant'=>$variant,'packs'=>$packs,'price_pence'=>$price,'subtotal_pence'=>$lineSubtotal,'vat_pence'=>$lineVat];
   $subtotal+=$lineSubtotal;$vat+=$lineVat;
  }
  $discount=0;$coupon=null;
  if($couponCode && $subtotal>0){
   $coupon=Coupon::where('code',strtoupper(trim($couponCode)))->first();
   if(!$coupon || !$coupon->applicable($subtotal))throw new \DomainException('This discount code is invalid, inactive, expired, or the minimum purchase was not reached.');
   if($coupon->max_uses!==null && $coupon->redeemed_count+$coupon->reserved_count >= $coupon->max_uses)throw new \DomainException('This discount code has reached its usage limit.');
   $discount=$coupon->amount($subtotal);
   if($discount===0)throw new \DomainException('This discount does not apply to the selected items.');
   // Allocate the discount across products to adjust VAT per taxable line.
   $allocated=0;$last=count($lines)-1;$vat=0;
   foreach($lines as $index=>&$line){
    $lineDiscount=$index===$last ? $discount-$allocated : intdiv($discount*$line['subtotal_pence'],$subtotal);
    $allocated+=$lineDiscount;
    $line['discount_pence']=$lineDiscount;
    $line['vat_pence']=intdiv(($line['subtotal_pence']-$lineDiscount)*(int)$line['variant']->vat_rate_bps+5000,10000);
    $vat+=$line['vat_pence'];
   }unset($line);
  }
  $delivery=($subtotal>=config('store.free_delivery_pence')||$subtotal===0)?0:(int)config('store.delivery_fee_pence');
  // Delivery tax varies by tax treatment and shipment: configure before production. Do not charge guessed delivery VAT here.
  return ['lines'=>$lines,'subtotal_pence'=>$subtotal,'delivery_pence'=>$delivery,'vat_pence'=>$vat,'discount_pence'=>$discount,'coupon'=>$coupon,'grand_total_pence'=>$subtotal-$discount+$delivery+$vat];
 }
}
