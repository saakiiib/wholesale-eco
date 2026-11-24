<?php
namespace App\Http\Controllers;

use App\Models\{Category,Product,ProductVariant};
use App\Services\CatalogueImportReader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class AdminProductImportController
{
    private function categories(): array
    {
        $all = Category::all()->keyBy('id');
        $paths = [];
        foreach ($all as $category) {
            $segments = [];
            $node = $category;
            $visited = [];
            while ($node) {
                if (isset($visited[$node->id])) break;
                $visited[$node->id] = true;
                array_unshift($segments, $node->name);
                $node = $node->parent_id ? $all->get($node->parent_id) : null;
            }
            $paths[$category->id] = implode(' > ', $segments);
        }
        asort($paths, SORT_NATURAL | SORT_FLAG_CASE);
        return $paths;
    }

    private function categoryId(string $path, array $categories): ?int
    {
        $normal = fn($text) => mb_strtolower(preg_replace('/\s+/', ' ', trim(preg_replace('/\s*>\s*/', ' > ', $text))));
        $matches = [];
        foreach ($categories as $id => $full) if ($normal($full) === $normal($path)) $matches[] = $id;
        return count($matches) === 1 ? (int)$matches[0] : null;
    }

    private function money(string $value): ?int
    {
        if (!preg_match('/^\d{1,6}(?:\.\d{1,2})?$/D', $value)) return null;
        [$whole,$fraction] = array_pad(explode('.', $value), 2, '');
        return (int)$whole * 100 + (int)str_pad($fraction, 2, '0');
    }

    public function index()
    {
        return view('portal.admin.product-import', ['categories'=>$this->categories(), 'preview'=>null]);
    }

    public function template()
    {
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'wb');
            fputcsv($out, CatalogueImportReader::HEADERS);
            fclose($out);
        }, 'wholesale-product-import-template.csv', ['Content-Type'=>'text/csv; charset=UTF-8']);
    }

    public function preview(Request $request, CatalogueImportReader $reader)
    {
        $request->validate(['file'=>['required','file','max:2048','mimes:csv,txt,xlsx']]);
        $file = $request->file('file');
        try { $rows = $reader->read($file->getRealPath(), $file->getClientOriginalExtension()); }
        catch (RuntimeException $e) { throw ValidationException::withMessages(['file'=>$e->getMessage()]); }
        $categories = $this->categories();
        $skus = []; $prepared = [];
        foreach ($rows as $row) {
            $errors = [];
            $line = $row['line'];
            if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]{1,99}$/D', $row['sku'])) $errors[] = 'SKU must be 2–100 letters/numbers, dots, dashes or underscores.';
            if (isset($skus[mb_strtolower($row['sku'])])) $errors[] = 'Duplicate SKU in this upload.';
            $skus[mb_strtolower($row['sku'])] = true;
            if ($row['product_name'] === '' || mb_strlen($row['product_name']) > 200) $errors[] = 'Product name is required (max 200 characters).';
            if ($row['variant_label'] === '' || mb_strlen($row['variant_label']) > 180) $errors[] = 'Variant label is required (max 180 characters).';
            if (!ctype_digit($row['units_per_pack']) || (int)$row['units_per_pack'] < 1 || (int)$row['units_per_pack'] > 1000000) $errors[] = 'Units per pack must be 1–1,000,000.';
            // Pallet ordering is optional; a pallet size is mandatory if enabled.
            $palletSize = $row['packs_per_pallet'];
            if ($palletSize !== '' && (!ctype_digit($palletSize) || (int)$palletSize < 1 || (int)$palletSize > 100000)) $errors[] = 'Packs per pallet must be 1–100,000 or blank.';
            foreach (['allow_pallet_orders','show_pallet_units'] as $flag) {
                if (!in_array(strtolower($row[$flag]), ['yes','no'], true)) $errors[] = str_replace('_',' ',ucfirst($flag)).' must be Yes or No.';
            }
            if (strtolower($row['allow_pallet_orders']) === 'yes' && ($palletSize === '' || !ctype_digit($palletSize) || (int)$palletSize < 1)) {
                $errors[] = 'Enter packs per pallet before enabling pallet orders.';
            }
            if (strtolower($row['show_pallet_units']) === 'yes' && ($palletSize === '' || !ctype_digit($palletSize) || (int)$palletSize < 1)) {
                $errors[] = 'Enter packs per pallet to display pallet units.';
            }
            $cost = $this->money($row['purchase_price_gbp']);
            if ($cost === null || $cost <= 0) $errors[] = 'Invalid purchase price (GBP excluding VAT).';
            if (!preg_match('/^\d{1,3}(?:\.\d{1,2})?$/D', $row['markup_percent']) || (float)$row['markup_percent'] > 500) $errors[] = 'Markup must be 0–500%.';
            if (!in_array($row['vat_percent'], ['0','5','20','0.00','5.00','20.00'], true)) $errors[] = 'VAT must be 0, 5 or 20%.';
            if ($row['stock_packs'] !== '' && (!ctype_digit($row['stock_packs']) || (float)$row['stock_packs'] > 1000000)) $errors[] = 'Stock must be a non-negative whole number.';
            if (mb_strlen($row['description']) > 10000) $errors[] = 'Description too long.';
            if (mb_strlen($row['category_path']) > 500) $errors[] = 'Category path too long.';
            $categoryId = $this->categoryId($row['category_path'], $categories);
            $existing = ProductVariant::with('product')->where('sku', $row['sku'])->first();
            if ($existing && $existing->product && $existing->product->name !== $row['product_name']) {
                $errors[] = 'Existing SKU belongs to a differently named product; resolve manually.';
            }
            $rate = (int)round(((float)$row['markup_percent']) * 100);
            $sell = $cost === null ? 0 : intdiv($cost * (10000 + $rate) + 5000, 10000);
            if ($sell > 4294967295) $errors[] = 'Selling price exceeds database capacity.';
            $prepared[] = compact('row','errors','categoryId','sell') + ['existing'=>(bool)$existing,'line'=>$line];
        }
        $token = Str::random(40);
        $request->session()->put('catalogue_import', ['token'=>$token,'rows'=>$prepared,'user_id'=>$request->user()->id,'created_at'=>now()->timestamp]);
        return view('portal.admin.product-import', ['categories'=>$categories,'preview'=>$prepared,'token'=>$token]);
    }

    public function commit(Request $request)
    {
        $batch = $request->session()->get('catalogue_import');
        if (!$batch || !hash_equals($batch['token'], (string)$request->input('token')) || $batch['user_id'] !== $request->user()->id || now()->timestamp - $batch['created_at'] > 1800) {
            throw ValidationException::withMessages(['file'=>'Preview expired. Please upload the file again.']);
        }
        $request->validate(['mode'=>'required|in:skip,update','publish'=>'required|in:0,1','category'=>'required|array','category.*'=>'required|integer|exists:categories,id']);
        $categories = $this->categories();
        $rows = $batch['rows'];
        if (count($request->input('category', [])) !== count($rows)) throw ValidationException::withMessages(['category'=>'A category must be selected for every row.']);
        foreach ($rows as $i=>$item) {
            if ($item['errors']) throw ValidationException::withMessages(['file'=>'Preview contains errors on line '.$item['line'].'. Correct the file and upload it again.']);
            if (!isset($categories[(int)($request->input('category')[$i] ?? 0)])) throw ValidationException::withMessages(['category'=>'Select a valid category for row '.($i+1).'.']);
            if (!Category::whereKey((int)$request->input('category')[$i])->where('active',true)->exists()) throw ValidationException::withMessages(['category'=>'An inactive category was selected for row '.($i+1).'.']);
        }
        $stats = ['created'=>0,'updated'=>0,'skipped'=>0];
        DB::transaction(function () use ($request,$rows,&$stats) {
            foreach ($rows as $i=>$item) {
                $r = $item['row'];
                $categoryId = (int)$request->input('category')[$i];
                $variant = ProductVariant::where('sku',$r['sku'])->lockForUpdate()->first();
                $vat = (int)round(((float)$r['vat_percent']) * 100);
                if ($variant) {
                    if ($request->input('mode') === 'skip') { $stats['skipped']++; continue; }
                    // Do not reassign existing products, change stock or discard tier discounts.
                    if ($variant->product?->name !== $r['product_name'] || (int)$variant->product?->category_id !== $categoryId) {
                        throw ValidationException::withMessages(['file'=>'SKU '.$r['sku'].' already exists under a different product/category. No changes were saved.']);
                    }
                    if ($variant->priceTiers()->exists()) throw ValidationException::withMessages(['file'=>'SKU '.$r['sku'].' has quantity-price tiers. Update through its product workspace instead. No changes were saved.']);
                    $variant->update(['base_pack_price_pence'=>$item['sell'],'vat_rate_bps'=>$vat]);
                    $stats['updated']++;
                    $subject = $variant;
                    $action = 'catalogue.bulk.updated';
                } else {
                    $product = Product::where('name',$r['product_name'])->where('category_id',$categoryId)->first();
                    if (!$product) {
                        $slug = Str::slug($r['product_name']);
                        if (!$slug) $slug = 'product';
                        $base = mb_substr($slug, 0, 160);
                        $slug = $base; $suffix=2;
                        while (Product::where('slug',$slug)->exists()) $slug = $base.'-'.$suffix++;
                        $product = Product::create(['name'=>$r['product_name'],'slug'=>$slug,'category_id'=>$categoryId,'description'=>$r['description'] ?: null,'active'=>$request->input('publish') === '1']);
                    }
                    $variant = ProductVariant::create(['product_id'=>$product->id,'sku'=>$r['sku'],'label'=>$r['variant_label'],'units_per_pack'=>(int)$r['units_per_pack'],'packs_per_pallet'=>$r['packs_per_pallet'] === '' ? null : (int)$r['packs_per_pallet'],'allow_pallet_orders'=>strtolower($r['allow_pallet_orders']) === 'yes','show_pallet_units'=>strtolower($r['show_pallet_units']) === 'yes','show_unit_prices'=>true,'base_pack_price_pence'=>$item['sell'],'vat_rate_bps'=>$vat,'stock_packs'=>$r['stock_packs'] === '' ? 0 : (int)$r['stock_packs'],'active'=>$request->input('publish') === '1']);
                    $stats['created']++;
                    $subject=$variant;
                    $action='catalogue.bulk.created';
                }
                DB::table('audit_logs')->insert(['user_id'=>$request->user()->id,'action'=>$action,'subject_type'=>'product_variant','subject_id'=>$subject->id,'changes'=>json_encode(['sku'=>$r['sku'],'category_id'=>$categoryId,'selling_price_pence'=>$item['sell'],'packs_per_pallet'=>$subject->packs_per_pallet,'allow_pallet_orders'=>(bool)$subject->allow_pallet_orders,'show_pallet_units'=>(bool)$subject->show_pallet_units,'source'=>'bulk-import']),'created_at'=>now(),'updated_at'=>now()]);
            }
        });
        $request->session()->forget('catalogue_import');
        return redirect()->route('admin.products.index')->with('success',"Import complete: {$stats['created']} new SKU(s), {$stats['updated']} price update(s), {$stats['skipped']} skipped.");
    }
}
