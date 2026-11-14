<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Business extends Model {
 protected $fillable=['name','trading_name','vat_number','phone','status'];

 public function users(){return $this->hasMany(User::class);}
 public function addresses(){return $this->hasMany(Address::class);}
 public function orders(){return $this->hasMany(Order::class);}
}
