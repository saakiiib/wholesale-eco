<?php
namespace App\Services;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
class PricingService {
 /** @return array{lines:array,subtotal_pence:int,delivery_pence:int,vat_pence:int,grand_total_pence:int} */
 public function quote(array $cart,?int $businessId=null):array {
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
  $delivery=($subtotal>=config('store.free_delivery_pence')||$subtotal===0)?0:(int)config('store.delivery_fee_pence');
  // Delivery tax varies by tax treatment and shipment: configure before production. Do not charge guessed delivery VAT here.
  return ['lines'=>$lines,'subtotal_pence'=>$subtotal,'delivery_pence'=>$delivery,'vat_pence'=>$vat,'grand_total_pence'=>$subtotal+$delivery+$vat];
 }
}
