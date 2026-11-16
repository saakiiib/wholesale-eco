<?php
namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class V14StockConsistencyTest extends TestCase
{
    use RefreshDatabase;

    private function variant(int $stock): ProductVariant
    {
        $product = Product::create(['name'=>'Stock Test Container','slug'=>'stock-test-container','active'=>true]);
        return ProductVariant::create([
            'product_id'=>$product->id,'sku'=>'STOCK-TEST-'.$stock,'label'=>'Case',
            'units_per_pack'=>200,'stock_packs'=>$stock,'base_pack_price_pence'=>1000,
            'vat_rate_bps'=>2000,'active'=>true,
        ]);
    }

    public function test_out_of_stock_sku_is_not_added_from_any_page(): void
    {
        $v=$this->variant(0);
        $this->get('/shop')->assertSee('Out of stock');
        $this->post('/basket/add',['variant_id'=>$v->id,'packs'=>1])->assertSessionHasErrors('packs');
        $this->assertEmpty(session('cart',[]));
    }

    public function test_existing_cart_quantity_counts_toward_available_stock(): void
    {
        $v=$this->variant(2);
        $this->post('/basket/add',['variant_id'=>$v->id,'packs'=>2])->assertSessionHas('cart');
        $this->post('/basket/add',['variant_id'=>$v->id,'packs'=>1])->assertSessionHasErrors('packs');
        $this->assertSame(2, (int)session('cart')[$v->id]);
    }

    public function test_cart_update_rejects_quantity_above_stock(): void
    {
        $v=$this->variant(2);
        $this->post('/basket/add',['variant_id'=>$v->id,'packs'=>1]);
        $this->post('/basket/update',['variant_id'=>$v->id,'packs'=>3])->assertSessionHasErrors('packs');
        $this->assertSame(1, (int)session('cart')[$v->id]);
    }

    public function test_inactive_parent_product_cannot_be_added(): void
    {
        $v=$this->variant(4);$v->product->update(['active'=>false]);
        $this->post('/basket/add',['variant_id'=>$v->id,'packs'=>1])->assertStatus(422);
    }
}
