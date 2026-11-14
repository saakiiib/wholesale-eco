<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PriceTier extends Model {
 protected $fillable=['product_variant_id','min_packs','unit_price_pence'];

 public function variant(){return $this->belongsTo(ProductVariant::class,'product_variant_id');}
}
