<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{Business,Order,User};
class PortalV3Test extends TestCase {
 use RefreshDatabase;
 private function trade(string $name):User {
  $b=Business::create(['name'=>$name]);
  return User::create(['business_id'=>$b->id,'name'=>'Trade User','email'=>strtolower(str_replace(' ','-',$name)).'@example.test','password'=>'test-password-123','role'=>'customer']);
 }
 public function test_trade_portal_pages_are_accessible():void {
  $this->actingAs($this->trade('Alpha Ltd'));
  foreach(['/account','/account/orders','/account/saved-lists','/account/addresses','/account/profile'] as $url)$this->get($url)->assertOk();
 }
 public function test_business_cannot_access_other_business_order():void {
  $a=$this->trade('Alpha Ltd');$b=$this->trade('Beta Ltd');
  $order=Order::create(['order_number'=>'TEST-001','business_id'=>$b->business_id,'email'=>$b->email,'business_name'=>'Beta Ltd','contact_name'=>'Beta','shipping_address'=>['line1'=>'street'],'status'=>'pending_payment','payment_status'=>'unpaid','subtotal_pence'=>100,'delivery_pence'=>0,'vat_pence'=>0,'grand_total_pence'=>100,'checkout_token'=>str_repeat('x',64)]);
  $this->actingAs($a)->get('/account/orders/'.$order->id)->assertForbidden();
  $this->actingAs($a)->get('/account/orders/'.$order->id.'/print')->assertForbidden();
 }
 public function test_customer_cannot_access_admin_v3():void {
  $this->actingAs($this->trade('Gamma Ltd'))->get('/admin/products')->assertForbidden();
 }
 public function test_admin_can_see_v3_pages():void {
  $admin=User::create(['name'=>'Owner','email'=>'owner@example.test','password'=>'test-password-123','role'=>'admin']);
  $this->actingAs($admin);
  foreach(['/admin','/admin/products','/admin/categories','/admin/customers','/admin/orders','/admin/reports'] as $url)$this->get($url)->assertOk();
 }
 public function test_suspended_business_cannot_access_portal():void {
  $u=$this->trade('Suspended Ltd');$u->business->update(['status'=>'suspended']);
  $this->actingAs($u)->get('/account')->assertForbidden();
 }
}
