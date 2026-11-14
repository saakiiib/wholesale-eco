<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Category extends Model {
 protected $fillable=['name','slug','active','parent_id','sort_order'];
 protected function casts():array{return ['active'=>'boolean'];}
 public function products(){return $this->hasMany(Product::class);}
}
