<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Address extends Model {
 protected $fillable=['business_id','label','contact_name','line1','line2','city','county','postcode','country','is_default'];
 protected function casts():array{return ['is_default'=>'boolean'];}
 public function business(){return $this->belongsTo(Business::class);}
}
