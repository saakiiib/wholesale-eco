<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Order extends Model {
 protected $fillable=['order_number','business_id','user_id','email','business_name','contact_name','phone','shipping_address','billing_address','status','payment_status','subtotal_pence','delivery_pence','vat_pence','grand_total_pence','coupon_id','discount_pence','currency','checkout_token','paid_at'];
 protected function casts():array{return ['shipping_address'=>'array','billing_address'=>'array','paid_at'=>'datetime','dispatch_email_sent_at'=>'datetime','dispatch_email_claimed_at'=>'datetime'];}
 public function items(){return $this->hasMany(OrderItem::class);}
 public function payment(){return $this->hasOne(Payment::class);}
 public function business(){return $this->belongsTo(Business::class);}
}
