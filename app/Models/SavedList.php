<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SavedList extends Model {
 protected $fillable=['business_id','name'];

 public function items(){return $this->hasMany(SavedListItem::class);}
}
