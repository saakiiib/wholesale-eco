<?php
namespace Tests\Feature;

use App\Models\{Category,Product,ProductVariant};
use Database\Seeders\SupplierKraftRangeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierKraftRangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_imports_all_twelve_source_skus_without_inventory_or_orders(): void
    {
        $this->seed(SupplierKraftRangeSeeder::class);
        $this->assertSame(12, ProductVariant::where('sku', 'like', 'WEP-%')->count());
        $this->assertSame(4, Product::where('slug', 'like', '%kraft%')->count());
        $this->assertSame(0, ProductVariant::where('sku', 'like', 'WEP-%')->where('stock_packs', '>', 0)->count());
        $this->assertDatabaseHas('product_variants', ['sku' => 'WEP-KB-1000', 'units_per_pack' => 300, 'base_pack_price_pence' => 1917, 'vat_rate_bps' => 2000]);
        $this->assertDatabaseHas('product_variants', ['sku' => 'WEP-BB-105', 'units_per_pack' => 200, 'base_pack_price_pence' => 1500]);
        $this->assertDatabaseHas('product_variants', ['sku' => 'WEP-RECTL-650', 'units_per_pack' => 600, 'base_pack_price_pence' => 3583]);
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_import_is_repeatable_and_does_not_overwrite_stock_or_price(): void
    {
        $this->seed(SupplierKraftRangeSeeder::class);
        $variant = ProductVariant::where('sku', 'WEP-KB-1000')->firstOrFail();
        $variant->update(['stock_packs' => 8, 'base_pack_price_pence' => 1850]);
        $this->seed(SupplierKraftRangeSeeder::class);
        $this->assertSame(12, ProductVariant::where('sku', 'like', 'WEP-%')->count());
        $this->assertDatabaseHas('product_variants', ['sku' => 'WEP-KB-1000', 'stock_packs' => 8, 'base_pack_price_pence' => 1850]);
    }

    public function test_nested_food_packaging_categories_are_available(): void
    {
        $this->seed(SupplierKraftRangeSeeder::class);
        $parent = Category::where('slug', 'boxes')->firstOrFail();
        $this->assertSame(4, Category::where('parent_id', $parent->id)->whereIn('slug', [
            'kraft-bowls-and-lids', 'kraft-clamshell-burger-boxes', 'kraft-deli-boxes', 'rectangular-kraft-boxes'
        ])->count());
    }
}
