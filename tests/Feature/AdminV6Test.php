<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{User,Business,CmsPage,StoreSetting,ShippingZone,Product,Category};
class AdminV6Test extends TestCase {
 use RefreshDatabase;
 private function admin():User{return User::create(['name'=>'Admin','email'=>'admin.v6@example.test','password'=>'password-for-tests123','role'=>'admin']);}
 public function test_customer_is_forbidden_from_admin_management():void {
  $business=Business::create(['name'=>'Café Limited']);
  $user=User::create(['name'=>'Buyer','email'=>'buyer.v6@example.test','password'=>'password-for-tests123','role'=>'customer','business_id'=>$business->id]);
  $this->actingAs($user);
  foreach(['/admin/settings','/admin/content','/admin/seo','/admin/shipping','/admin/team','/admin/audit-log'] as $url)$this->get($url)->assertForbidden();
 }
 public function test_admin_can_access_management_sections():void {
  $this->actingAs($this->admin());
  foreach(['/admin','/admin/settings','/admin/content','/admin/seo','/admin/shipping','/admin/team','/admin/audit-log'] as $url)$this->get($url)->assertOk();
 }
 public function test_business_settings_are_persisted_and_homepage_copy_is_visible():void {
  $this->actingAs($this->admin());
  $this->put('/admin/settings',['business_name'=>'Wholesale Eco Products','business_email'=>'admin@example.test','low_stock_threshold'=>8,'hero_title'=>'Wholesale supplies made easy'])->assertRedirect();
  $this->assertDatabaseHas('store_settings',['key'=>'hero_title','value'=>'Wholesale supplies made easy']);
  $this->get('/')->assertSee('Wholesale supplies made easy');
 }
 public function test_published_content_page_is_public_and_draft_is_not():void {
  $this->actingAs($this->admin());
  $this->post('/admin/content',['title'=>'Delivery','slug'=>'delivery-information','body'=>'Delivery details','published'=>'1'])->assertRedirect();
  $this->get('/pages/delivery-information')->assertOk()->assertSee('Delivery details');
  CmsPage::where('slug','delivery-information')->firstOrFail()->update(['published'=>false]);
  $this->get('/pages/delivery-information')->assertNotFound();
 }
 public function test_shipping_zone_can_be_saved_but_is_not_assumed_live():void {
  $this->actingAs($this->admin());
  $this->post('/admin/shipping',['name'=>'Central','postcode_prefixes'=>'MK, LU','base_rate_pence'=>1295,'free_above_pence'=>15000,'priority'=>10])->assertRedirect();
  $this->assertDatabaseHas('shipping_zones',['name'=>'Central','base_rate_pence'=>1295]);
 }
 public function test_seo_metadata_can_be_saved_for_a_product():void {
  $this->actingAs($this->admin());
  $product=Product::create(['name'=>'Test Paper Cups','slug'=>'test-paper-cups','active'=>true]);
  $this->put('/admin/seo',['type'=>'product','id'=>$product->id,'meta_title'=>'Buy Test Paper Cups','meta_description'=>'Case of test cups'])->assertRedirect();
  $this->assertDatabaseHas('products',['id'=>$product->id,'meta_title'=>'Buy Test Paper Cups']);
 }
 public function test_self_demotion_is_rejected():void {
  $admin=$this->admin();$this->actingAs($admin);
  $this->put('/admin/team/'.$admin->id,['role'=>'staff'])->assertStatus(422);
 }
}
