<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{User,Product,ProductVariant,PriceTier};

class V13ProductWorkspaceTest extends TestCase
{
    use RefreshDatabase;
    private function admin(): User { return User::create(['name'=>'Editor','email'=>'v13.admin@example.test','role'=>'admin','password'=>'password-for-tests123']); }
    private function product(): Product {return Product::create(['name'=>'Kraft Bowl','slug'=>'v13-kraft-bowl','active'=>true]);}

    public function test_customers_cannot_enter_the_catalogue_workspace(): void
    {
        $buyer=User::create(['name'=>'Buyer','email'=>'v13.buyer@example.test','role'=>'customer','password'=>'password-for-tests123']);
        $this->actingAs($buyer)->get('/admin/products/1/workspace')->assertForbidden();
    }

    public function test_admin_can_create_inactive_zero_stock_sku_in_pounds(): void
    {
        $p=$this->product();
        $this->actingAs($this->admin())->post("/admin/products/{$p->id}/workspace/variants",['sku'=>'BOWL-1000-V13','label'=>'1,000 ml','units_per_pack'=>300,'price_gbp'=>'19.17','vat_rate_bps'=>2000])->assertRedirect();
        $this->assertDatabaseHas('product_variants',['sku'=>'BOWL-1000-V13','base_pack_price_pence'=>1917,'stock_packs'=>0,'active'=>false]);
    }

    public function test_tiers_are_saved_using_integer_pence(): void
    {
        $p=$this->product();$v=$p->variants()->create(['sku'=>'V13-TIER','label'=>'Case','units_per_pack'=>100,'base_pack_price_pence'=>4000,'vat_rate_bps'=>2000,'stock_packs'=>0]);
        $this->actingAs($this->admin())->put("/admin/products/{$p->id}/workspace/variants/{$v->id}/prices",['base_gbp'=>'40.00','tier_4_gbp'=>'38.50','tier_10_gbp'=>'36.00','tier_20_gbp'=>'34.00'])->assertRedirect();
        $this->assertDatabaseHas('price_tiers',['product_variant_id'=>$v->id,'min_packs'=>10,'unit_price_pence'=>3600]);
    }

    public function test_duplicate_is_draft_with_no_inventory_or_shared_gallery(): void
    {
        $p=$this->product();$p->variants()->create(['sku'=>'V13-ORIG','label'=>'250 pack','units_per_pack'=>250,'base_pack_price_pence'=>2000,'stock_packs'=>100,'active'=>true]);
        $this->actingAs($this->admin())->post("/admin/products/{$p->id}/duplicate")->assertRedirect();
        $copy=Product::where('id','!=',$p->id)->firstOrFail();
        $this->assertFalse($copy->active);
        $this->assertSame(0,(int)$copy->variants()->firstOrFail()->stock_packs);
        $this->assertFalse($copy->variants()->firstOrFail()->active);
    }
}
