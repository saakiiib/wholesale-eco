<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Product extends Model {
 protected $fillable=['category_id','name','slug','description','image_path','active','meta_title','meta_description'];
 protected function casts():array{return ['active'=>'boolean'];}
 public function images(){return $this->hasMany(ProductImage::class)->orderBy('sort_order');}
 public function variants(){return $this->hasMany(ProductVariant::class);}
 public function category(){return $this->belongsTo(Category::class);}
}
