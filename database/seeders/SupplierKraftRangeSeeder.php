<?php
namespace Database\Seeders;

use App\Models\{Category, Product, ProductVariant, ProductImage};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Imports exactly the 12 purchasable SKUs in the client-supplied
 * "Kraft bowls and takeaway boxes" range sheet (14 November 2026).
 *
 * IMPORTANT: source prices INCLUDE 20% VAT.  This application stores ex-VAT
 * pack prices, so calculate the nearest penny with integer arithmetic.
 * Import does NOT create a sale, invoice, transaction, stock, quantity tiers,
 * pallet discounts, or change existing user-edited SKU prices.
 */
class SupplierKraftRangeSeeder extends Seeder
{
    private function exVatPence(int $inclusivePence): int
    {
        // round(inclusive / 1.20) without floating-point imprecision
        return intdiv($inclusivePence * 5 + 3, 6);
    }

    public function run(): void
    {
        DB::transaction(function (): void {
            $parent = Category::firstOrCreate(
                ['slug' => 'boxes'],
                ['name' => 'Food Packaging', 'sort_order' => 1, 'active' => true]
            );

            $ranges = [
                [
                    'category' => ['kraft-bowls-and-lids', 'Kraft Bowls & Lids'],
                    'product' => ['kraft-bowls-and-pp-lids', 'Kraft Bowls & Matching PP Lids', 'Kraft bowls for hot and cold food, with separate matching polypropylene lids. Choose the exact bowl or lid size required. Lids and bowls are sold separately.', 'catalogue/kraft-bowls.jpg'],
                    'photos' => ['catalogue/kraft-bowls.jpg'],
                    'variants' => [
                        ['WEP-KB-1000', '1,000 ml kraft bowl', 300, 2300, ['capacity_ml' => 1000, 'material' => 'Kraft board']],
                        ['WEP-KB-1300', '1,300 ml kraft bowl', 300, 3250, ['capacity_ml' => 1300, 'material' => 'Kraft board']],
                        ['WEP-KBL-150', '150 mm PP lid (for 1,000 ml bowl)', 300, 1950, ['diameter_mm' => 150, 'fits' => 'WEP-KB-1000', 'material' => 'PP']],
                        ['WEP-KBL-185', '185 mm PP lid (for 1,300 ml bowl)', 300, 2500, ['diameter_mm' => 185, 'fits' => 'WEP-KB-1300', 'material' => 'PP']],
                    ],
                ],
                [
                    'category' => ['kraft-clamshell-burger-boxes', 'Clamshell Burger Boxes'],
                    'product' => ['kraft-clamshell-burger-boxes', 'Kraft Clamshell Burger Boxes', 'Hinged kraft clamshell takeaway burger boxes. Dimensions: 105 × 105 × 85 mm. Suitable for burgers and similar handheld main dishes.', 'catalogue/kraft-burger-box.jpg'],
                    'photos' => ['catalogue/kraft-burger-box.jpg', 'catalogue/kraft-burger-box-open.jpg'],
                    'variants' => [
                        ['WEP-BB-105', '105 × 105 × 85 mm kraft clamshell burger box', 200, 1800, ['length_mm' => 105, 'width_mm' => 105, 'height_mm' => 85, 'style' => 'Hinged clamshell']],
                    ],
                ],
                [
                    'category' => ['kraft-deli-boxes', 'Kraft Deli Boxes'],
                    'product' => ['kraft-deli-takeaway-boxes', 'Kraft Deli Takeaway Boxes', 'Kraft-board deli boxes for noodles, rice and side dishes. Choose No. 1 (750 ml) or No. 2 (1,400 ml).', 'catalogue/kraft-deli-boxes.jpg'],
                    'photos' => ['catalogue/kraft-deli-boxes.jpg'],
                    'variants' => [
                        ['WEP-DELI-1', 'No. 1 deli box · 750 ml', 450, 3650, ['capacity_ml' => 750, 'size' => 'No. 1', 'material' => 'Kraft board']],
                        ['WEP-DELI-2', 'No. 2 deli box · 1,400 ml', 200, 2700, ['capacity_ml' => 1400, 'size' => 'No. 2', 'material' => 'Kraft board']],
                    ],
                ],
                [
                    'category' => ['rectangular-kraft-boxes', 'Rectangular Kraft Boxes & Lids'],
                    'product' => ['rectangular-kraft-takeaway-boxes-and-lids', 'Rectangular Kraft Takeaway Boxes & Matching Lids', 'Rectangular kraft takeaway boxes for mains, sides and meal deals. Matching polypropylene lids are sold separately and are specified for hot and cold food in the supplier range sheet.', 'catalogue/rectangular-kraft-boxes.jpg'],
                    'photos' => ['catalogue/rectangular-kraft-boxes.jpg', 'catalogue/rectangular-kraft-filled.jpg'],
                    'variants' => [
                        ['WEP-RECT-500', '500 ml rectangular kraft box', 300, 2600, ['capacity_ml' => 500, 'material' => 'Kraft board']],
                        ['WEP-RECT-650', '650 ml rectangular kraft box', 600, 5200, ['capacity_ml' => 650, 'material' => 'Kraft board']],
                        ['WEP-RECT-750', '750 ml rectangular kraft box', 300, 2750, ['capacity_ml' => 750, 'material' => 'Kraft board']],
                        ['WEP-RECTL-500750', 'PP lid for 500 ml and 750 ml boxes', 300, 2150, ['fits' => 'WEP-RECT-500,WEP-RECT-750', 'material' => 'PP']],
                        ['WEP-RECTL-650', 'PP lid for 650 ml box', 600, 4300, ['fits' => 'WEP-RECT-650', 'material' => 'PP']],
                    ],
                ],
            ];

            foreach ($ranges as $range) {
                [$catSlug, $catName] = $range['category'];
                $category = Category::firstOrCreate(
                    ['slug' => $catSlug],
                    ['name' => $catName, 'parent_id' => $parent->id, 'active' => true]
                );
                [$slug, $name, $description, $imagePath] = $range['product'];
                $product = Product::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => $name, 'description' => $description, 'category_id' => $category->id, 'image_path' => $imagePath, 'active' => true]
                );
                // Existing products and their merchant-edited records are not overwritten.
                // Main image is already in products.image_path; only persist additional gallery photos.
                foreach (array_slice($range['photos'], 1) as $index => $path) {
                    ProductImage::firstOrCreate(
                        ['product_id' => $product->id, 'path' => $path],
                        ['alt_text' => $name . ' · supplier range photograph', 'sort_order' => $index]
                    );
                }
                foreach ($range['variants'] as [$sku, $label, $units, $inclusive, $attributes]) {
                    $variant = ProductVariant::firstOrCreate(
                        ['sku' => $sku],
                        [
                            'product_id' => $product->id,
                            'label' => $label,
                            'attributes' => $attributes,
                            'units_per_pack' => $units,
                            'base_pack_price_pence' => $this->exVatPence($inclusive),
                            'vat_rate_bps' => 2000,
                            'stock_packs' => 0, // stock was not supplied; must be confirmed by admin
                            'active' => true,
                        ]
                    );
                    if ($variant->product_id !== $product->id) {
                        throw new \RuntimeException('SKU collision for ' . $sku . '; import aborted, please inspect existing data.');
                    }
                }
            }
        });
    }
}
