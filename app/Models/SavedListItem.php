<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SavedListItem extends Model {
 // The saved_list_items table intentionally stores no created_at/updated_at columns.
 public $timestamps = false;
 protected $fillable=['saved_list_id','product_variant_id','packs'];

 public function variant(){return $this->belongsTo(ProductVariant::class,'product_variant_id');}
}
