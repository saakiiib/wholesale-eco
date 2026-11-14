<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ProductVariant extends Model {
 protected $fillable=['product_id','sku','label','attributes','units_per_pack','packs_per_pallet','base_pack_price_pence','vat_rate_bps','stock_packs','active'];
 protected function casts():array{return ['attributes'=>'array','active'=>'boolean'];}
 public function product(){return $this->belongsTo(Product::class);}
 public function priceTiers(){return $this->hasMany(PriceTier::class)->orderByDesc('min_packs');}
}
