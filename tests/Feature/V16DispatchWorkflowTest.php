<?php
namespace Tests\Feature;

use App\Mail\OrderDispatched;
use App\Models\{Order,Payment,StoreSetting,User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class V16DispatchWorkflowTest extends TestCase {
 use RefreshDatabase;
 private function admin():User {return User::create(['name'=>'Orders Admin','email'=>'v16-admin@example.test','password'=>'test-password-123','role'=>'admin']);}
 private function order(string $payment='paid'):Order {
  return Order::create(['order_number'=>'WEP-V16-'.strtoupper(substr(md5(uniqid('',true)),0,8)),
   'email'=>'buyer-v16@example.test','business_name'=>'Example Cafe Ltd','contact_name'=>'Customer',
   'shipping_address'=>['line1'=>'12 High Street','city'=>'Lincoln','postcode'=>'LN1 1AA','country'=>'GB'],
   'status'=>'processing','payment_status'=>$payment,'subtotal_pence'=>5000,'delivery_pence'=>0,
   'vat_pence'=>1000,'grand_total_pence'=>6000,'checkout_token'=>bin2hex(random_bytes(32))]);
 }
 public function test_dispatch_without_tracking_sends_customer_email_once():void {
  Mail::fake();$order=$this->order();$this->actingAs($this->admin());
  $this->post(route('admin.orders.status',$order),['status'=>'dispatched'])->assertRedirect();
  $this->assertDatabaseHas('orders',['id'=>$order->id,'status'=>'dispatched','dispatch_email_status'=>'sent']);
  Mail::assertSent(OrderDispatched::class,1);
  Mail::assertSent(OrderDispatched::class,fn($mail)=>$mail->carrier===null && $mail->trackingNumber===null && $mail->hasTo('buyer-v16@example.test'));
  $this->post(route('admin.orders.status',$order),['status'=>'dispatched'])->assertRedirect();
  Mail::assertSent(OrderDispatched::class,1);
 }
 public function test_dispatch_with_tracking_saves_it_and_sends_in_same_action():void {
  Mail::fake();$order=$this->order();$this->actingAs($this->admin());
  $this->post(route('admin.orders.status',$order),['status'=>'dispatched','carrier'=>'DPD','tracking_number'=>'DPD-001'])->assertRedirect();
  $this->assertDatabaseHas('shipments',['order_id'=>$order->id,'carrier'=>'DPD','tracking_number'=>'DPD-001']);
  Mail::assertSent(OrderDispatched::class,fn($mail)=>$mail->carrier==='DPD' && $mail->trackingNumber==='DPD-001');
 }
 public function test_unpaid_order_cannot_be_dispatched():void {
  Mail::fake();$order=$this->order('unpaid');$this->actingAs($this->admin());
  $this->post(route('admin.orders.status',$order),['status'=>'dispatched'])->assertStatus(422);
  Mail::assertNothingSent();
 }
 public function test_customer_cannot_dispatch_orders():void {
  Mail::fake();$order=$this->order();$buyer=User::create(['name'=>'Buyer','email'=>'v16-buyer@example.test','password'=>'test-password-123','role'=>'customer']);
  $this->actingAs($buyer)->post(route('admin.orders.status',$order),['status'=>'dispatched'])->assertForbidden();
  Mail::assertNothingSent();
 }
 public function test_payment_provider_appears_next_to_paid_status():void {
  $order=$this->order();Payment::create(['order_id'=>$order->id,'provider'=>'paypal','status'=>'paid','amount_pence'=>6000]);
  $this->actingAs($this->admin())->get(route('admin.orders.index'))->assertOk()->assertSee('PayPal');
  $this->get(route('admin.orders.show',$order))->assertOk()->assertSee('PayPal');
 }
 public function test_invoice_footer_shows_store_contact_details():void {
  $order=$this->order();
  foreach(['business_phone'=>'01522 275285','business_email'=>'support@wholesaleecoproducts.co.uk','business_address'=>'11 Clifton Street, Lincoln LN5 8LQ'] as $key=>$value)StoreSetting::create(compact('key','value'));
  $this->actingAs($this->admin())->get(route('admin.orders.invoice',$order))->assertOk()->assertSee('01522 275285')->assertSee('support@wholesaleecoproducts.co.uk');
 }
}
