<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ShippingZone extends Model {protected $fillable=['name','postcode_prefixes','base_rate_pence','free_above_pence','enabled','priority'];protected function casts():array{return ['enabled'=>'boolean'];}}
