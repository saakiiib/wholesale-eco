<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Artisan;
use App\Models\{Business,User};
class DemoSeederTest extends TestCase {
 use DatabaseMigrations;
 private array $orig=[];
 protected function setUp():void {
  parent::setUp();
  foreach(['DEMO_ADMIN_EMAIL','DEMO_ADMIN_PASSWORD','DEMO_USER_PASSWORD'] as $k)$this->orig[$k]=getenv($k);
  putenv('DEMO_ADMIN_EMAIL=admin@example.com');
  putenv('DEMO_ADMIN_PASSWORD=test-admin-pass-123');
  putenv('DEMO_USER_PASSWORD=test-user-pass-123');
  $this->seed(\Database\Seeders\DemoUsersSeeder::class);
 }
 protected function tearDown():void {
  foreach($this->orig as $k=>$v){$v===false?putenv($k):putenv("$k=$v");}
  parent::tearDown();
 }
 public function test_demo_admin_can_log_in_and_reach_the_dashboard():void {
  $this->post('/login',['email'=>'admin@example.com','password'=>'test-admin-pass-123'])->assertRedirect('/admin');
  $this->get('/admin')->assertOk()->assertSee('Demo Admin');
  foreach(['/admin/products','/admin/categories','/admin/customers','/admin/orders','/admin/reports'] as $url)$this->get($url)->assertOk();
 }
 public function test_demo_trade_customers_can_log_in_and_reach_the_account_portal():void {
  foreach(['riverside@example.test','events@example.test'] as $email){
   $this->post('/login',['email'=>$email,'password'=>'test-user-pass-123'])->assertRedirect('/account');
   $this->assertAuthenticatedAs(User::where('email',$email)->first());
   $this->get('/account')->assertOk();
  }
 }
 public function test_suspended_demo_customer_cannot_log_in():void {
  $this->post('/login',['email'=>'suspended@example.test','password'=>'test-user-pass-123'])
   ->assertRedirect('/')->assertSessionHasErrors('email');
  $this->assertGuest();
 }
 public function test_demo_seeder_is_idempotent():void {
  $before=User::count();
  $this->seed(\Database\Seeders\DemoUsersSeeder::class);
  $this->assertSame($before,User::count());
  $this->assertSame(3,Business::whereIn('name',['Riverside Café Ltd','Green Events Catering','Closed Shop Ltd'])->count());
 }
 public function test_demo_reset_command_reseeds_demo_accounts():void {
  User::where('email','admin@example.com')->delete();
  $this->assertSame(0,User::where('email','admin@example.com')->count());
  $this->assertSame(0,Artisan::call('demo:reset',['--force'=>true]));
  $this->assertSame(1,User::where('email','admin@example.com')->where('role','admin')->count());
 }
}
