<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Product extends Model {
 protected $fillable=['category_id','name','slug','description','image_path','active'];
 protected function casts():array{return ['active'=>'boolean'];}
 public function variants(){return $this->hasMany(ProductVariant::class);}
 public function category(){return $this->belongsTo(Category::class);}
}
