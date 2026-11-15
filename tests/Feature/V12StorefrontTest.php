<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{Category,Product,User};

class V12StorefrontTest extends TestCase {
 use RefreshDatabase;
 public function test_category_has_single_search_box_and_compact_matrix_headers():void {
  $category=Category::create(['name'=>'Kraft Bowls','slug'=>'kraft-bowls','active'=>true]);
  $this->get('/shop?category=kraft-bowls')->assertOk()
   ->assertSee('Find the right product')->assertSee('Search all products')
   ->assertDontSee('Search all products in this category')
   ->assertDontSee('class="v9-spec"',false);
 }
 public function test_public_sitemap_contains_active_categories():void {
  Category::create(['name'=>'Bowls','slug'=>'bowls','active'=>true]);
  $this->get('/sitemap.xml')->assertOk()->assertSee('shop?category=bowls');
 }
 public function test_non_admin_cannot_upload_product_gallery():void {
  $user=User::create(['name'=>'Customer','email'=>'v12customer@example.test','role'=>'customer','password'=>'example-password-123']);
  $product=Product::create(['name'=>'Test bowl','slug'=>'test-bowl','active'=>true]);
  $this->actingAs($user)->post('/admin/products/'.$product->id.'/images',[])->assertForbidden();
 }
 public function test_login_post_is_rate_limited():void {
  for($i=0;$i<10;$i++){$this->post('/login',['email'=>'bad@example.test','password'=>'incorrect']);}
  $this->post('/login',['email'=>'bad@example.test','password'=>'incorrect'])->assertStatus(429);
 }
}
