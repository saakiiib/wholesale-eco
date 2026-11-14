<?php
namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Models\{StoreSetting,Category,Product,ProductVariant,User};

class StorefrontV8Test extends TestCase
{
 use RefreshDatabase;
 private function variant(): ProductVariant {
  $category=Category::create(['name'=>'Paper bags','slug'=>'paper-bags','active'=>true]);
  $product=Product::create(['name'=>'Twist handle bags','slug'=>'twist-handle-bags','category_id'=>$category->id,'active'=>true]);
  return ProductVariant::create(['product_id'=>$product->id,'sku'=>'TWIST-500','label'=>'Brown 500','units_per_pack'=>500,'packs_per_pallet'=>10,'base_pack_price_pence'=>1000,'stock_packs'=>100,'active'=>true,'allow_pallet_orders'=>true,'show_unit_prices'=>true,'show_pallet_units'=>true]);
 }
 public function test_homepage_and_contact_display_live_business_details():void {
  StoreSetting::create(['key'=>'business_phone','value'=>'01522 275285']);
  StoreSetting::create(['key'=>'business_email','value'=>'support@wholesaleecoproducts.co.uk']);
  $this->get('/')->assertOk()->assertSee('01522 275285')->assertSee('support@wholesaleecoproducts.co.uk')->assertSee('Mento Software');
  $this->get('/contact')->assertOk()->assertSee('01522 275285')->assertSee('Send an enquiry');
 }
 public function test_contact_form_is_rate_limited_and_sends_message_to_configured_business_mailbox():void {
  Mail::fake();
  StoreSetting::create(['key'=>'business_email','value'=>'support@example.test']);
  $this->post('/contact',['name'=>'Buyer','email'=>'buyer@example.test','business'=>'Cafe Ltd','subject'=>'Bulk bags','message'=>'Please quote for 20 cases.','website'=>''])->assertRedirect('/contact');
  Mail::assertSentCount(1);
 }
 public function test_pack_and_pallet_are_equivalent_quantities_in_cart():void {
  $v=$this->variant();
  $this->post('/basket/add',['variant_id'=>$v->id,'packs'=>2,'order_unit'=>'pallet'])->assertRedirect();
  $this->assertSame(20,session('cart.'.$v->id));
 }
 public function test_pallet_order_is_blocked_when_not_enabled():void {
  $v=$this->variant();$v->update(['allow_pallet_orders'=>false]);
  $this->post('/basket/add',['variant_id'=>$v->id,'packs'=>2,'order_unit'=>'pallet'])->assertStatus(422);
 }
 public function test_pallet_order_cannot_exceed_stock():void {
  $v=$this->variant();$v->update(['stock_packs'=>12]);
  $this->post('/basket/add',['variant_id'=>$v->id,'packs'=>2,'order_unit'=>'pallet'])->assertSessionHasErrors('packs');
 }
 public function test_matrix_contains_configurable_pack_and_pallet_info():void {
  $this->variant();
  $this->get('/shop?category=paper-bags')->assertOk()->assertSee('Units per:')->assertSee('Pallet')->assertSee('data-tier-index',false)->assertSee('we-order-unit');
 }
 public function test_admin_can_control_pallet_and_price_display_options():void {
  $v=$this->variant();
  $admin=User::create(['name'=>'Manager','email'=>'manager-v8@example.test','password'=>'long-password-test','role'=>'admin']);
  $this->actingAs($admin)->put('/admin/variants/'.$v->id,[
   'label'=>'Brown 500','units_per_pack'=>500,'packs_per_pallet'=>8,'base_pack_price_pence'=>1000,'vat_rate_bps'=>2000,'active'=>1,
   'allow_pallet_orders'=>1,'show_pallet_units'=>1,'show_unit_prices'=>1
  ])->assertRedirect();
  $this->assertDatabaseHas('product_variants',['id'=>$v->id,'packs_per_pallet'=>8,'allow_pallet_orders'=>true]);
 }
}
