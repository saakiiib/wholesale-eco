<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\{Category,Product,ProductVariant,PriceTier,Business,User};
class DatabaseSeeder extends Seeder {
 public function run():void {
  $catalog=[
   ['Cups & Lids','cups',[['12oz Double Wall Kraft Coffee Cups','CUP-12-DW',1000,4600],['12oz Coffee Cup Lids','LID-12-BLK',1000,2800]]],
   ['Food Packaging','boxes',[['Kraft Burger Boxes','BOX-BURGER',200,3400],['Kraft Takeaway Containers','BOX-750ML',300,4200]]],
   ['Bags & Carriers','bags',[['Brown Twist Handle Carrier Bags','BAG-BT-110',500,5800],['White Twist Handle Carrier Bags','BAG-WT-110',500,7100]]],
   ['Napkins & Tableware','napkins',[['White 2-Ply Napkins','NAP-2PLY',2000,1850],['Wooden Cutlery Sets','CUT-WOOD',500,2900]]],
   ['Bowls & Trays','bowls',[['Kraft Salad Bowls','BOWL-750',300,4800]]],
   ['Wraps & Film','wraps',[['Greaseproof Food Sheets','WRAP-GRS',1000,2150]]],
   ['Cleaning & Hygiene','hygiene',[['Blue Cleaning Rolls','HYGIENE-ROLL',6,1695]]],
  ];
  foreach($catalog as $idx=>[$name,$slug,$items]){
   $category=Category::firstOrCreate(['slug'=>$slug],['name'=>$name,'sort_order'=>$idx]);
   foreach($items as [$productName,$sku,$units,$price]){
    $product=Product::firstOrCreate(['slug'=>strtolower($sku)],['name'=>$productName,'description'=>'Wholesale foodservice supplies. Sample catalogue item.','category_id'=>$category->id]);
    $variant=ProductVariant::firstOrCreate(['sku'=>$sku],['product_id'=>$product->id,'label'=>'Standard case','units_per_pack'=>$units,'base_pack_price_pence'=>$price,'vat_rate_bps'=>2000,'stock_packs'=>250]);
    foreach([4=>95,10=>90,20=>85] as $min=>$pct){PriceTier::updateOrCreate(['product_variant_id'=>$variant->id,'min_packs'=>$min],['unit_price_pence'=>(int)round($price*$pct/100)]);}
   }
  }
  // No hard-coded or publicly known admin password. Use artisan tinker to provision administrator.
 }
}
