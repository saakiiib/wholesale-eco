<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OrderItem extends Model {
 protected $fillable=['order_id','product_variant_id','sku','product_name','variant_label','packs','units_per_pack','unit_pack_price_pence','line_subtotal_pence','line_vat_pence','vat_rate_bps'];

 public function variant(){return $this->belongsTo(ProductVariant::class,'product_variant_id');}
}
