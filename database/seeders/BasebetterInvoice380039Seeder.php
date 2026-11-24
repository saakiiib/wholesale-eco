<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Basebetter invoice INV-380039 (21 November 2026): 18 distinct purchasable packs.
 *
 * Run only this seeder using:
 *   php artisan db:seed --class=Database\\Seeders\\BasebetterInvoice380039Seeder
 *
 * Safe to rerun: records are created only if absent; existing prices, stock,
 * images, attributes, variant settings and customer-specific prices are untouched.
 *
 * ATTENTION:
 * - All supplier invoice unit costs EXCLUDE VAT. Selling pack price = cost + 20% markup.
 * - Source supplies purchased quantities, NOT guaranteed present-day inventory.
 *   Default seeded stock is ZERO. To use original quantities as OPENING stock,
 *   set WHOLESALE_INV380039_USE_INVOICE_QUANTITIES=true explicitly for first run.
 * - Pallet counts and manufacturer SKUs are not supplied: pallet ordering is OFF.
 * - Invoice contains NO product photos. Use a verified local image manifest; no
 *   substitute or remote images will be invented.
 * - Products start hidden (inactive) pending merchant approval and photography.
 * - Quantity discount tiers are not seeded because the invoice specifies none.
 */
class BasebetterInvoice380039Seeder extends Seeder
{
    private const SOURCE = 'INV-380039';
    private const MARKUP_PERCENT = 20;
    private const VAT_BPS = 2000;

    public function run(): void
    {
        $useInvoiceStock = filter_var(env('WHOLESALE_INV380039_USE_INVOICE_QUANTITIES', false), FILTER_VALIDATE_BOOLEAN);
        DB::transaction(function () use ($useInvoiceStock): void {
            $root = $this->category('boxes', 'Food Packaging', null);
            $cups = $this->category('cups', 'Cups & Lids', null);
            $categories = [
                'kraft-bowls-and-lids' => $this->category('kraft-bowls-and-lids', 'Kraft Bowls & Lids', $root->id),
                'kraft-deli-boxes-invoice' => $this->category('kraft-deli-boxes-invoice', 'Kraft Deli Boxes', $root->id),
                'bagasse-takeaway-packaging' => $this->category('bagasse-takeaway-packaging', 'Bagasse Takeaway Packaging', $root->id),
                'cups' => $cups,
            ];

            foreach ($this->items() as [$sku, $name, $categoryKey, $units, $costPence, $invoiceBoxes, $attributes]) {
                $category = $categories[$categoryKey];
                $slug = 'basebetter-inv380039-' . strtolower(substr($sku, -2));
                $description = $this->description($name, $units);
                $product = Product::where('slug', $slug)->first();
                $existingSku = ProductVariant::where('sku', $sku)->first();

                // Never attach our invoice SKU to another merchant product.
                if ($existingSku && (!$product || (int) $existingSku->product_id !== (int) $product->id)) {
                    throw new RuntimeException('SKU collision: ' . $sku . '. Resolve before seeding.');
                }
                if ($product && !$existingSku && $product->variants()->exists()) {
                    throw new RuntimeException('Product slug collision: ' . $slug . '. Resolve before seeding.');
                }

                if (!$product) {
                    $product = Product::create([
                        'slug' => $slug,
                        'category_id' => $category->id,
                        'name' => $name,
                        'description' => $description,
                        'image_path' => null,
                        'active' => false,
                        'meta_title' => $name . ' | Wholesale Eco Products',
                        'meta_description' => Str::limit($description, 155, ''),
                        'sort_order' => 100,
                    ]);
                }

                if (!$existingSku) {
                    // Integer arithmetic ensures deterministic rounding to the nearest penny.
                    $sellingPence = intdiv($costPence * (100 + self::MARKUP_PERCENT) + 50, 100);
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $sku,
                        'label' => 'Box of ' . number_format($units) . ' units',
                        'attributes' => array_merge($attributes, [
                            'purchase_cost_gbp' => number_format($costPence / 100, 2, '.', ''),
                            'markup_percent' => self::MARKUP_PERCENT,
                            'invoice_purchased_boxes' => $invoiceBoxes,
                            'sku_is_provisional' => true,
                        ]),
                        'units_per_pack' => $units,
                        'packs_per_pallet' => null,
                        'allow_pallet_orders' => false,
                        'show_unit_prices' => true,
                        'show_pallet_units' => false,
                        'base_pack_price_pence' => $sellingPence,
                        'vat_rate_bps' => self::VAT_BPS,
                        'stock_packs' => $useInvoiceStock ? $invoiceBoxes : 0,
                        'active' => false,
                    ]);
                }

                // The supplier invoice has no photographs. Only connect locally
                // supplied, verified paths if the merchant explicitly adds them.
                foreach ($this->verifiedPhotos()[$sku] ?? [] as $sort => $path) {
                    if (!Storage::disk('public')->exists($path)) {
                        throw new RuntimeException('Missing image on public disk: ' . $path);
                    }
                    ProductImage::firstOrCreate(
                        ['product_id' => $product->id, 'path' => $path],
                        ['alt_text' => $name, 'sort_order' => $sort]
                    );
                    if ($sort === 0 && !$product->image_path) {
                        $product->update(['image_path' => $path]);
                    }
                }
            }
        });
    }

    private function category(string $slug, string $name, ?int $parentId): Category
    {
        $category = Category::firstOrCreate(
            ['slug' => $slug],
            ['name' => $name, 'parent_id' => $parentId, 'active' => true]
        );
        if ((int) ($category->parent_id ?? 0) !== (int) ($parentId ?? 0)) {
            throw new RuntimeException('Existing category slug has unexpected parent: ' . $slug);
        }
        return $category;
    }

    private function description(string $name, int $units): string
    {
        return $name . '. Wholesale pack containing ' . number_format($units) . ' units. '
            . 'Supplier invoice ' . self::SOURCE . '. Confirm technical specifications, '
            . 'product imagery and live stock before publishing.';
    }

    /**
     * Optional merchant-supplied images (relative to storage/app/public).
     * Example: 'BB-INV380039-01' => ['products/basebetter/1300ml-bowl.jpg'],
     * Do not add a filename unless the actual product photo has been uploaded.
     * No image rows are created while this array is empty.
     */
    private function verifiedPhotos(): array
    {
        return [];
    }

    /**
     * [provisional SKU, invoice line name, category key, units per box,
     *  supplier cost in pennies ex VAT, invoice box quantity, known attributes].
     */
    private function items(): array
    {
        return [
            ['BB-INV380039-01', '1300ml Kraft Bowl - 300pk', 'kraft-bowls-and-lids', 300, 2250, 18, ['source_invoice' => 'INV-380039', 'supplier' => 'Basebetter (UK) Limited', 'pack_unit' => 'box', 'material' => 'kraft', 'capacity_ml' => 1300]],
            ['BB-INV380039-02', 'PP 185mm LID to fit 1100/1300ml Bowl - 300pk', 'kraft-bowls-and-lids', 300, 1750, 18, ['source_invoice' => 'INV-380039', 'supplier' => 'Basebetter (UK) Limited', 'pack_unit' => 'box', 'material' => 'polypropylene (PP)', 'diameter_mm' => 185]],
            ['BB-INV380039-03', '1000ml Kraft Bowl - 300pk', 'kraft-bowls-and-lids', 300, 1600, 10, ['source_invoice' => 'INV-380039', 'supplier' => 'Basebetter (UK) Limited', 'pack_unit' => 'box', 'material' => 'kraft', 'capacity_ml' => 1000]],
            ['BB-INV380039-04', '750ml Kraft Bowl - 300pk', 'kraft-bowls-and-lids', 300, 1450, 2, ['source_invoice' => 'INV-380039', 'supplier' => 'Basebetter (UK) Limited', 'pack_unit' => 'box', 'material' => 'kraft', 'capacity_ml' => 750]],
            ['BB-INV380039-05', '500ml Kraft Bowl - 300pk', 'kraft-bowls-and-lids', 300, 1380, 2, ['source_invoice' => 'INV-380039', 'supplier' => 'Basebetter (UK) Limited', 'pack_unit' => 'box', 'material' => 'kraft', 'capacity_ml' => 500]],
            ['BB-INV380039-06', 'PP 150mm LID to fit 500/750/1000ml Bowl - 300pk', 'kraft-bowls-and-lids', 300, 1350, 14, ['source_invoice' => 'INV-380039', 'supplier' => 'Basebetter (UK) Limited', 'pack_unit' => 'box', 'material' => 'polypropylene (PP)', 'diameter_mm' => 150]],
            ['BB-INV380039-07', '750ml TA Boxes No.1 - 450pk', 'kraft-deli-boxes-invoice', 450, 2550, 3, ['source_invoice' => 'INV-380039', 'supplier' => 'Basebetter (UK) Limited', 'pack_unit' => 'box', 'capacity_ml' => 750]],
            ['BB-INV380039-08', '1000ml TA Boxes No.5 - 200pk', 'kraft-deli-boxes-invoice', 200, 1680, 2, ['source_invoice' => 'INV-380039', 'supplier' => 'Basebetter (UK) Limited', 'pack_unit' => 'box', 'capacity_ml' => 1000]],
            ['BB-INV380039-09', '1300ml TA Boxes No.8 - 200pk', 'kraft-deli-boxes-invoice', 200, 1750, 2, ['source_invoice' => 'INV-380039', 'supplier' => 'Basebetter (UK) Limited', 'pack_unit' => 'box', 'capacity_ml' => 1300]],
            ['BB-INV380039-10', '8oz Sauce Cup Kraft - 500pk', 'cups', 500, 1600, 2, ['source_invoice' => 'INV-380039', 'supplier' => 'Basebetter (UK) Limited', 'pack_unit' => 'box', 'material' => 'kraft', 'capacity_oz' => 8]],
            ['BB-INV380039-11', 'PP Lid to fit 8oz/16oz soup cup Dia97 - 500pk', 'cups', 500, 1650, 2, ['source_invoice' => 'INV-380039', 'supplier' => 'Basebetter (UK) Limited', 'pack_unit' => 'box', 'material' => 'polypropylene (PP)']],
            ['BB-INV380039-12', '2-Comp PP Insert to fit 750ml/1000ml Kraft Bowl - 300pk', 'kraft-bowls-and-lids', 300, 920, 1, ['source_invoice' => 'INV-380039', 'supplier' => 'Basebetter (UK) Limited', 'pack_unit' => 'box', 'material' => 'polypropylene (PP)', 'compartments' => 2]],
            ['BB-INV380039-13', '3-Comp PP Insert to fit 750ml/1000ml Kraft Bowl - 300pk', 'kraft-bowls-and-lids', 300, 920, 1, ['source_invoice' => 'INV-380039', 'supplier' => 'Basebetter (UK) Limited', 'pack_unit' => 'box', 'material' => 'polypropylene (PP)', 'compartments' => 3]],
            ['BB-INV380039-14', 'Bagasse Burger Box 6x6in - 500pk', 'bagasse-takeaway-packaging', 500, 1980, 1, ['source_invoice' => 'INV-380039', 'supplier' => 'Basebetter (UK) Limited', 'pack_unit' => 'box', 'material' => 'bagasse', 'dimensions_in' => '6 x 6']],
            ['BB-INV380039-15', 'PP 185mm Insert to fit 1300ml Bowl - 300pk', 'kraft-bowls-and-lids', 300, 1250, 1, ['source_invoice' => 'INV-380039', 'supplier' => 'Basebetter (UK) Limited', 'pack_unit' => 'box', 'material' => 'polypropylene (PP)', 'diameter_mm' => 185]],
            ['BB-INV380039-16', 'Bagasse TA Box 7.5x6in - 500pk', 'bagasse-takeaway-packaging', 500, 2550, 1, ['source_invoice' => 'INV-380039', 'supplier' => 'Basebetter (UK) Limited', 'pack_unit' => 'box', 'material' => 'bagasse', 'dimensions_in' => '7.5 x 6']],
            ['BB-INV380039-17', 'Bagasse TA Box 7x5in - 500pk', 'bagasse-takeaway-packaging', 500, 2050, 1, ['source_invoice' => 'INV-380039', 'supplier' => 'Basebetter (UK) Limited', 'pack_unit' => 'box', 'material' => 'bagasse', 'dimensions_in' => '7 x 5']],
            ['BB-INV380039-18', 'Bagasse TA Box 9x6in - 250pk', 'bagasse-takeaway-packaging', 250, 1620, 1, ['source_invoice' => 'INV-380039', 'supplier' => 'Basebetter (UK) Limited', 'pack_unit' => 'box', 'material' => 'bagasse', 'dimensions_in' => '9 x 6']],
        ];
    }
}
