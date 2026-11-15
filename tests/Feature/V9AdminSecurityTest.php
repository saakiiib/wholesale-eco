<?php
namespace Tests\Feature;
use Tests\TestCase;
use App\Models\{User,AdminRole};
use Illuminate\Foundation\Testing\RefreshDatabase;
class V9AdminSecurityTest extends TestCase {
 use RefreshDatabase;
 public function test_customer_cannot_see_admin_roles():void{
  $u=User::create(['name'=>'Customer','email'=>'customer-v9@example.test','password'=>'strongpassword123','role'=>'customer']);
  $this->actingAs($u)->get('/admin/roles')->assertForbidden();
 }
 public function test_restricted_staff_cannot_redefine_their_own_role():void{
  $role=AdminRole::create(['name'=>'Orders','slug'=>'orders','permissions'=>['orders.manage']]);
  $u=User::create(['name'=>'Orders staff','email'=>'staff-v9@example.test','password'=>'strongpassword123','role'=>'staff','admin_role_id'=>$role->id]);
  $this->actingAs($u)->get('/admin/orders')->assertOk();
  $this->actingAs($u)->get('/admin/products')->assertForbidden();
  $this->actingAs($u)->put('/admin/roles/'.$role->id,['name'=>'Superstaff','permissions'=>['team.manage']])->assertForbidden();
 }
}
