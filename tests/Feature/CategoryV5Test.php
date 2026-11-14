<?php
namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{Category,Product,ProductVariant,PriceTier};

class CategoryV5Test extends TestCase {
 use RefreshDatabase;

 public function test_category_page_shows_v4_layout_and_wholesale_price_matrix():void {
  $category=Category::create(['name'=>'Food Packaging','slug'=>'boxes','active'=>true]);
  $p=Product::create(['category_id'=>$category->id,'name'=>'Kraft Burger Boxes','slug'=>'burger-box-test','active'=>true]);
  $v=ProductVariant::create(['product_id'=>$p->id,'sku'=>'BOX-TEST','label'=>'Medium','units_per_pack'=>200,'base_pack_price_pence'=>3400,'stock_packs'=>10,'active'=>true]);
  PriceTier::create(['product_variant_id'=>$v->id,'min_packs'=>4,'unit_price_pence'=>3200]);
  $this->get('/shop?category=boxes')->assertOk()->assertSee('Choose your packaging options')->assertSee('Kraft Burger Boxes')->assertSee('BOX-TEST')->assertSee('£34.00')->assertSee('£32.00')->assertSee('name="variant_id"',false);
 }

 public function test_parent_category_includes_child_and_grandchild_products():void {
  $bags=Category::create(['name'=>'Bags & Carriers','slug'=>'bags','active'=>true]);
  $paper=Category::create(['name'=>'Paper Bags','slug'=>'paper-bags','parent_id'=>$bags->id,'active'=>true]);
  $twist=Category::create(['name'=>'Tape & Twist Handle Paper Carrier Bags','slug'=>'tape-twist-bags','parent_id'=>$paper->id,'active'=>true]);
  $p=Product::create(['category_id'=>$twist->id,'name'=>'Brown Twist Handle Bags','slug'=>'brown-twist-test','active'=>true]);
  ProductVariant::create(['product_id'=>$p->id,'sku'=>'BAG-TWIST-TEST','label'=>'250 per case','units_per_pack'=>250,'base_pack_price_pence'=>2900,'stock_packs'=>10,'active'=>true]);
  $this->get('/shop?category=bags')->assertOk()->assertSee('Brown Twist Handle Bags');
  $this->get('/shop?category=paper-bags')->assertOk()->assertSee('Brown Twist Handle Bags');
  $this->get('/shop?category=tape-twist-bags')->assertOk()->assertSee('Brown Twist Handle Bags')->assertSee('Paper Bags');
 }

 public function test_invalid_category_slug_returns_not_found():void {
  $this->get('/shop?category=unknown-category')->assertNotFound();
 }
}
