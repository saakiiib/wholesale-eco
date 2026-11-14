<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
class AccessTest extends TestCase {
 use RefreshDatabase;
 public function test_admin_requires_authentication():void{$this->get('/admin')->assertRedirect('/login');}
 public function test_customer_cannot_access_admin():void{
  $user=User::create(['name'=>'Test','email'=>'test@example.test','password'=>'a-strong-test-password','role'=>'customer']);
  $this->actingAs($user)->get('/admin')->assertForbidden();
 }
 public function test_public_catalogue_works():void{$this->get('/shop')->assertOk();}
}
