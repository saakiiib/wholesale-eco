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
 * Basebetter invoice INV-380039: 18 purchasable packs grouped into 8 products.
 *
 *   php artisan db:seed --class=Database\\Seeders\\BasebetterInvoice380039Seeder
 *
 * Rerun-safe: products, variants are set ACTIVE and stock_packs is set to the
 * invoice/xlsx quantity. Existing prices, images and attributes are kept.
 */
class BasebetterInvoice380039Seeder extends Seeder
{
    private const SOURCE = 'INV-380039';
    private const MARKUP_PERCENT = 20;
    private const VAT_BPS = 2000;

    public function run(): void
    {
        DB::transaction(function (): void {
            $root = $this->category('boxes', 'Food Packaging', null);
            $cups = $this->category('cups', 'Cups & Lids', null);
            $categories = [
                'kraft-bowls-and-lids' => $this->category('kraft-bowls-and-lids', 'Kraft Bowls & Lids', $root->id),
                'kraft-deli-boxes-invoice' => $this->category('kraft-deli-boxes-invoice', 'Kraft Deli Boxes', $root->id),
                'bagasse-takeaway-packaging' => $this->category('bagasse-takeaway-packaging', 'Bagasse Takeaway Packaging', $root->id),
                'cups' => $cups,
            ];

            $groups = $this->productGroups();
            foreach ($groups as $key => $group) {
                [$slug, $name, $categoryKey, $description] = $group;
                $product = Product::where('slug', $slug)->first();
                if (!$product) {
                    $product = Product::create([
                        'slug' => $slug,
                        'category_id' => $categories[$categoryKey]->id,
                        'name' => $name,
                        'description' => $description,
                        'image_path' => null,
                        'active' => true,
                        'meta_title' => Str::limit($name . ' | Wholesale Eco Products', 250, ''),
                        'meta_description' => Str::limit($description, 155, ''),
                        'sort_order' => 100,
                    ]);
                } elseif ((int) $product->category_id !== (int) $categories[$categoryKey]->id) {
                    throw new RuntimeException('Grouped product category mismatch for ' . $slug . '. Review manually.');
                } elseif (!$product->active) {
                    $product->update(['active' => true]);
                }
                $groups[$key]['product'] = $product;
            }

            foreach ($this->items() as [$sku, $name, $categoryKey, $units, $costPence, $invoiceBoxes, $attributes]) {
                $groupKey = $this->groupForSku($sku);
                $product = $groups[$groupKey]['product'];
                $oldSlug = 'basebetter-inv380039-' . strtolower(substr($sku, -2));
                $variant = ProductVariant::where('sku', $sku)->first();

                if ($variant) {
                    if ((int) $variant->product_id !== (int) $product->id) {
                        $old = Product::findOrFail($variant->product_id);
                        if ($old->slug !== $oldSlug || $old->name !== $name || ($variant->attributes['source_invoice'] ?? null) !== self::SOURCE
                            || $old->images()->exists() || $old->variants()->count() !== 1) {
                            throw new RuntimeException('Cannot automatically regroup edited SKU ' . $sku . '. Resolve manually.');
                        }
                        $variant->update(['product_id' => $product->id]);
                        $old->delete();
                    }
                    // Activate and set stock from the invoice/xlsx.
                    $variant->update([
                        'active' => true,
                        'stock_packs' => $invoiceBoxes,
                    ]);
                } else {
                    $legacy = Product::where('slug', $oldSlug)->first();
                    if ($legacy && ($legacy->images()->exists() || $legacy->variants()->exists())) {
                        throw new RuntimeException('Conflicting legacy product ' . $oldSlug . '. Resolve manually.');
                    }
                    if ($legacy) $legacy->delete();

                    $sellingPence = intdiv($costPence * (100 + self::MARKUP_PERCENT) + 50, 100);
                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $sku,
                        'label' => $this->variantLabel($sku, $name),
                        'attributes' => array_merge($attributes, [
                            'purchase_cost_gbp' => number_format($costPence / 100, 2, '.', ''),
                            'markup_percent' => self::MARKUP_PERCENT,
                            'invoice_purchased_boxes' => $invoiceBoxes,
                            'sku_is_provisional' => true,
                            'variant_option' => $this->variantLabel($sku, $name),
                        ]),
                        'units_per_pack' => $units,
                        'packs_per_pallet' => null,
                        'allow_pallet_orders' => false,
                        'show_unit_prices' => true,
                        'show_pallet_units' => false,
                        'base_pack_price_pence' => $sellingPence,
                        'vat_rate_bps' => self::VAT_BPS,
                        'stock_packs' => $invoiceBoxes,
                        'active' => true,
                    ]);
                }

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

    /** [slug, name, category key, description] */
    private function productGroups(): array
    {
        return [
            'bowls' => ['basebetter-kraft-bowls', 'Kraft Bowls – 500ml to 1300ml', 'kraft-bowls-and-lids', 'Kraft food bowls in four capacities. Select 500ml, 750ml, 1000ml or 1300ml; 300 bowls per box. Lids sold separately.'],
            'bowl-lids' => ['basebetter-pp-bowl-lids', 'PP Lids for Kraft Bowls', 'kraft-bowls-and-lids', 'Polypropylene lids in 150mm and 185mm sizes. Select the correct diameter for your bowl; 300 lids per box.'],
            'deli-boxes' => ['basebetter-deli-boxes', 'Takeaway Boxes – No.1, No.5 & No.8', 'kraft-deli-boxes-invoice', 'Takeaway boxes in 750ml, 1000ml and 1300ml variants. Pack quantities vary by size; see selected option.'],
            'sauce-cups' => ['basebetter-sauce-cups', 'Kraft Sauce Cups – 8oz', 'cups', 'Kraft sauce cups, 8oz size, 500 per box.'],
            'soup-lids' => ['basebetter-soup-cup-lids', 'PP Soup Cup Lids – 97mm', 'cups', 'Polypropylene lids for 8oz and 16oz soup cups, 97mm diameter, 500 per box.'],
            'bowl-inserts' => ['basebetter-bowl-inserts', 'PP Inserts for Kraft Bowls', 'kraft-bowls-and-lids', 'Polypropylene bowl inserts in 2-compartment, 3-compartment and 185mm options. Confirm bowl compatibility for each selection.'],
            'burger-boxes' => ['basebetter-bagasse-burger-box', 'Bagasse Burger Box – 6×6in', 'bagasse-takeaway-packaging', 'Bagasse burger boxes, 6×6 inch, 500 per box.'],
            'bagasse-boxes' => ['basebetter-bagasse-takeaway-boxes', 'Bagasse Takeaway Boxes', 'bagasse-takeaway-packaging', 'Bagasse takeaway boxes with 7.5×6, 7×5 and 9×6 inch options. Pack quantity varies by size.'],
        ];
    }

    private function groupForSku(string $sku): string
    {
        $number = (int) substr($sku, -2);
        return match ($number) {
            1, 3, 4, 5 => 'bowls',
            2, 6 => 'bowl-lids',
            7, 8, 9 => 'deli-boxes',
            10 => 'sauce-cups',
            11 => 'soup-lids',
            12, 13, 15 => 'bowl-inserts',
            14 => 'burger-boxes',
            16, 17, 18 => 'bagasse-boxes',
            default => throw new RuntimeException('Unknown invoice SKU: ' . $sku),
        };
    }

    private function variantLabel(string $sku, string $name): string
    {
        $labels = [
            '01' => '1300ml · 300/box',
            '02' => '185mm PP lid · 300/box',
            '03' => '1000ml · 300/box',
            '04' => '750ml · 300/box',
            '05' => '500ml · 300/box',
            '06' => '150mm PP lid · 300/box',
            '07' => 'No.1 · 750ml · 450/box',
            '08' => 'No.5 · 1000ml · 200/box',
            '09' => 'No.8 · 1300ml · 200/box',
            '10' => '8oz · 500/box',
            '11' => '97mm · 500/box',
            '12' => '2 compartments · 300/box',
            '13' => '3 compartments · 300/box',
            '14' => '6×6in · 500/box',
            '15' => '185mm insert · 300/box',
            '16' => '7.5×6in · 500/box',
            '17' => '7×5in · 500/box',
            '18' => '9×6in · 250/box',
        ];
        return $labels[substr($sku, -2)] ?? $name;
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
        if (!$category->active) {
            $category->update(['active' => true]);
        }
        return $category;
    }

    /** Optional images relative to storage/app/public, e.g. 'BB-INV380039-01' => ['products/basebetter/1300ml-bowl.jpg'] */
    private function verifiedPhotos(): array
    {
        return [];
    }

    /**
     * [SKU, invoice line name, category key, units per box,
     *  supplier cost in pennies ex VAT, stock (boxes) from xlsx, attributes].
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