<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SavedListItem extends Model {
 protected $fillable=['saved_list_id','product_variant_id','packs'];

 public function variant(){return $this->belongsTo(ProductVariant::class,'product_variant_id');}
}
