<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{Product,ProductVariant,Category,Coupon,User};
use App\Services\PricingService;
class V11CommerceTest extends TestCase {
 use RefreshDatabase;
 private function variant():ProductVariant{
  $p=Product::create(['name'=>'Kraft Bowl','slug'=>'kraft-bowl','active'=>true,'sort_order'=>20]);
  return ProductVariant::create(['product_id'=>$p->id,'sku'=>'BOWL-TEST','label'=>'300 case','units_per_pack'=>300,'stock_packs'=>100,'base_pack_price_pence'=>1000,'vat_rate_bps'=>2000,'active'=>true]);
 }
 public function test_fixed_coupon_reduces_vat_and_order_total():void{
  $v=$this->variant();Coupon::create(['code'=>'SAVE5','discount_type'=>'fixed','discount_value'=>500,'minimum_subtotal_pence'=>0,'max_uses'=>1,'active'=>true]);
  $q=app(PricingService::class)->quote([$v->id=>1],null,'SAVE5');
  $this->assertSame(500,$q['discount_pence']);$this->assertSame(100,$q['vat_pence']);
  $this->assertSame(500+$q['delivery_pence']+100,$q['grand_total_pence']);
 }
 public function test_percentage_coupon_applies_to_product_price():void{
  $v=$this->variant();Coupon::create(['code'=>'TENPERCENT','discount_type'=>'percent','discount_value'=>1000,'minimum_subtotal_pence'=>0,'active'=>true]);
  $q=app(PricingService::class)->quote([$v->id=>2],null,'TENPERCENT');
  $this->assertSame(200,$q['discount_pence']);$this->assertSame(360,$q['vat_pence']);
 }
 public function test_one_off_coupon_rejects_existing_reservation():void{
  $v=$this->variant();Coupon::create(['code'=>'ONCE','discount_type'=>'fixed','discount_value'=>100,'minimum_subtotal_pence'=>0,'max_uses'=>1,'reserved_count'=>1,'active'=>true]);
  $this->expectException(\DomainException::class);
  app(PricingService::class)->quote([$v->id=>1],null,'ONCE');
 }
 public function test_customer_cannot_manage_coupons():void{
  $user=User::create(['name'=>'Customer','email'=>'v11customer@example.test','role'=>'customer','password'=>'example-password-123']);
  $this->actingAs($user)->get('/admin/coupons')->assertForbidden();
 }
 public function test_admin_can_sort_categories_with_up_down_actions():void{
  $admin=User::create(['name'=>'Admin','email'=>'v11admin@example.test','role'=>'admin','password'=>'example-password-123']);
  $a=Category::create(['name'=>'A','slug'=>'a','sort_order'=>10]);$b=Category::create(['name'=>'Boxes','slug'=>'boxes','sort_order'=>20]);
  $this->actingAs($admin)->post('/admin/categories/'.$b->id.'/move',['direction'=>'up'])->assertRedirect();
  $this->assertSame($b->id,Category::orderBy('sort_order')->first()->id);
 }
}
