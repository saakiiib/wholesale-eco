<?php
namespace App\Http\Controllers;

use App\Models\{Product,ProductVariant,PriceTier,Category};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Reusable product workspace: keeps SKU stock separate from price and metadata changes. */
class ProductWorkspaceController
{
    private function audit(Request $request, string $action, string $type, int $id): void
    {
        DB::table('audit_logs')->insert(['user_id'=>$request->user()->id,'action'=>$action,'subject_type'=>$type,'subject_id'=>$id,'changes'=>'{}','created_at'=>now(),'updated_at'=>now()]);
    }

    public function show(Product $product)
    {
        $product->load(['category','variants.priceTiers','images']);
        return view('portal.admin.product-workspace', ['product'=>$product,'categories'=>Category::orderBy('sort_order')->orderBy('name')->get()]);
    }

    public function addVariant(Request $request, Product $product)
    {
        $data=$request->validate([
          'sku'=>['required','string','max:100',Rule::unique('product_variants','sku')],
          'label'=>'required|string|max:180', 'units_per_pack'=>'required|integer|min:1|max:1000000',
          'packs_per_pallet'=>'nullable|integer|min:1|max:100000',
          'price_gbp'=>['required','regex:/^\d{1,7}(\.\d{1,2})?$/'],
          'vat_rate_bps'=>'required|integer|in:0,500,2000',
          'capacity_ml'=>'nullable|integer|min:1|max:100000',
          'length_mm'=>'nullable|integer|min:1|max:100000', 'width_mm'=>'nullable|integer|min:1|max:100000',
          'height_mm'=>'nullable|integer|min:1|max:100000','material'=>'nullable|string|max:100',
          'type'=>'nullable|string|max:100',
        ]);
        $price=$this->pence($data['price_gbp']);
        $attrs=array_filter(['capacity_ml'=>$data['capacity_ml']??null,'length_mm'=>$data['length_mm']??null,'width_mm'=>$data['width_mm']??null,'height_mm'=>$data['height_mm']??null,'material'=>$data['material']??null,'type'=>$data['type']??null],fn($v)=>$v!==null&&$v!=='');
        $variant=DB::transaction(function()use($product,$data,$attrs,$price){
          return $product->variants()->create(['sku'=>$data['sku'],'label'=>$data['label'],'units_per_pack'=>$data['units_per_pack'],'packs_per_pallet'=>$data['packs_per_pallet']??null,'base_pack_price_pence'=>$price,'vat_rate_bps'=>$data['vat_rate_bps'],'attributes'=>$attrs,'stock_packs'=>0,'active'=>false,'allow_pallet_orders'=>false,'show_unit_prices'=>true,'show_pallet_units'=>false]);
        });
        $this->audit($request,'catalogue.variant.created','product_variant',$variant->id);
        return redirect()->route('admin.workspace.show',$product)->with('success','Draft SKU created; review prices and stock, then activate in Variants.');
    }

    public function savePrices(Request $request, Product $product, ProductVariant $variant)
    {
        abort_unless($variant->product_id===$product->id,404);
        $data=$request->validate([
          'base_gbp'=>['required','regex:/^\d{1,7}(\.\d{1,2})?$/'],
          'tier_4_gbp'=>['nullable','regex:/^\d{1,7}(\.\d{1,2})?$/'],
          'tier_10_gbp'=>['nullable','regex:/^\d{1,7}(\.\d{1,2})?$/'],
          'tier_20_gbp'=>['nullable','regex:/^\d{1,7}(\.\d{1,2})?$/'],
        ]);
        // Do not silently discard custom pricing breaks made in the legacy editor.
        if ($variant->priceTiers()->whereNotIn('min_packs',[1,4,10,20])->exists()) {
            return back()->withErrors(['prices'=>'This SKU has custom price breaks. Edit them in Advanced Pricing before using the standard tier editor.']);
        }
        $base=$this->pence($data['base_gbp']);
        $tiers=[];
        foreach([4,10,20] as $qty){$key='tier_'.$qty.'_gbp';if(isset($data[$key])&&$data[$key]!=='')$tiers[$qty]=$this->pence($data[$key]);}
        // Prevent ambiguous or increasing quantity-tier prices.
        $previous=$base;
        foreach($tiers as $amount){if($amount>$previous)return back()->withErrors(['prices'=>'Tier prices cannot increase at higher order quantities.'])->withInput();$previous=$amount;}
        DB::transaction(function()use($variant,$base,$tiers){
          $variant->update(['base_pack_price_pence'=>$base]);
          PriceTier::where('product_variant_id',$variant->id)->whereIn('min_packs',[1,4,10,20])->delete();
          foreach($tiers as $qty=>$amount){PriceTier::create(['product_variant_id'=>$variant->id,'min_packs'=>$qty,'unit_price_pence'=>$amount]);}
        });
        $this->audit($request,'catalogue.prices.updated','product_variant',$variant->id);
        return back()->with('success','Wholesale case-price tiers saved.');
    }

    public function duplicate(Request $request, Product $product)
    {
        $copy=DB::transaction(function()use($product){
          $new=$product->replicate(['slug','image_path','active']);
          $new->name='Copy of '.$product->name;
          $new->slug=Str::slug($product->slug.'-copy-'.Str::lower(Str::random(7)));
          $new->active=false;
          $new->image_path=null;
          $new->save();
          foreach($product->variants()->with('priceTiers')->get() as $v){
            $sku=$v->replicate(['sku','stock_packs','active']);
            $sku->product_id=$new->id;
            $sku->sku=substr($v->sku,0,80).'-COPY-'.Str::upper(Str::random(6));
            $sku->stock_packs=0;$sku->active=false;$sku->save();
            foreach($v->priceTiers as $tier)$sku->priceTiers()->create(['min_packs'=>$tier->min_packs,'unit_price_pence'=>$tier->unit_price_pence]);
          }
          return $new;
        });
        $this->audit($request,'catalogue.product.duplicated','product',$copy->id);
        return redirect()->route('admin.workspace.show',$copy)->with('success','Draft product duplicated. Photos and stock were deliberately not copied. Replace SKU codes before activating.');
    }

    public function export()
    {
        return response()->streamDownload(function(){
          $out=fopen('php://output','w');fputcsv($out,['Product','Product slug','Category','Published','SKU','Variant','Units/pack','Packs/pallet','VAT %','Base GBP ex VAT','4+ GBP','10+ GBP','20+ GBP','Stock packs','Active SKU']);
          ProductVariant::with(['product.category','priceTiers'])->orderBy('id')->chunk(250,function($variants)use($out){foreach($variants as $v){$tiers=$v->priceTiers->keyBy('min_packs');$safe=fn($s)=>preg_match('/^[=+@\-\t\r]/u',(string)$s)?"'".$s:$s;fputcsv($out,[$safe($v->product->name),$safe($v->product->slug),$safe($v->product->category?->name??''),$v->product->active?'Yes':'No',$safe($v->sku),$safe($v->label),$v->units_per_pack,$v->packs_per_pallet,$v->vat_rate_bps/100,number_format($v->base_pack_price_pence/100,2,'.',''),...array_map(fn($min)=>isset($tiers[$min])?number_format($tiers[$min]->unit_price_pence/100,2,'.',''):'',[4,10,20]),$v->stock_packs,$v->active?'Yes':'No']);}});
          fclose($out);
        },'wholesale-catalogue-'.now()->format('Ymd').'.csv',['Content-Type'=>'text/csv; charset=UTF-8']);
    }

    private function pence(string $value): int
    {
        [$pounds,$pennies]=array_pad(explode('.',$value,2),2,'');
        $n=((int)$pounds)*100+(int)str_pad($pennies,2,'0');
        if($n<1||$n>100000000)throw \Illuminate\Validation\ValidationException::withMessages(['price_gbp'=>'Price must be between £0.01 and £1,000,000.']);
        return $n;
    }
}
