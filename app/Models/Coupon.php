<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Coupon extends Model {
 protected $fillable=['code','discount_type','discount_value','minimum_subtotal_pence','max_discount_pence','max_uses','starts_at','expires_at','active'];
 protected function casts():array{return ['active'=>'boolean','starts_at'=>'datetime','expires_at'=>'datetime'];}
 public function applicable(int $subtotal):bool{return $this->active&&$subtotal >= $this->minimum_subtotal_pence&&(!$this->starts_at||!$this->starts_at->isFuture())&&(!$this->expires_at||$this->expires_at->isFuture());}
 public function amount(int $subtotal):int {return min($subtotal,$this->max_discount_pence??PHP_INT_MAX,$this->discount_type==='percent' ? intdiv($subtotal*$this->discount_value+5000,10000) : $this->discount_value);}
}
