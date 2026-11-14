<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{Product,ProductVariant,PriceTier};
use App\Services\PricingService;
class PricingTest extends TestCase {
 use RefreshDatabase;
 public function test_four_cases_receive_the_volume_tier():void{
  $p=Product::create(['name'=>'Cup','slug'=>'cup']);
  $v=ProductVariant::create(['product_id'=>$p->id,'sku'=>'CU-1','label'=>'Standard','units_per_pack'=>100,'stock_packs'=>30,'base_pack_price_pence'=>1000,'vat_rate_bps'=>2000]);
  PriceTier::create(['product_variant_id'=>$v->id,'min_packs'=>4,'unit_price_pence'=>900]);
  $q=app(PricingService::class)->quote([$v->id=>4]);
  $this->assertSame(3600,$q['subtotal_pence']);$this->assertSame(720,$q['vat_pence']);
 }
}
