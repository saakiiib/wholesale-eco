<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Category extends Model {
 protected $fillable=['name','slug','active','parent_id','sort_order','description','meta_title','meta_description'];
 protected function casts():array{return ['active'=>'boolean'];}
 public function parent(){return $this->belongsTo(self::class,'parent_id');}
 public function children(){return $this->hasMany(self::class,'parent_id')->orderBy('sort_order')->orderBy('name');}
 public function products(){return $this->hasMany(Product::class);}
}
