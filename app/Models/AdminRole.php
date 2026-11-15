<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AdminRole extends Model {
 protected $fillable=['name','slug','permissions'];
 protected function casts():array {return ['permissions'=>'array'];}
 public function users(){return $this->hasMany(User::class,'admin_role_id');}
 public static function permissionLabels():array{return [
  'dashboard.view'=>'Dashboard & analytics','orders.manage'=>'Orders & fulfilment',
  'catalogue.manage'=>'Products, variants & categories','inventory.manage'=>'Inventory & wholesale pricing',
  'customers.manage'=>'Trade customers','settings.manage'=>'Business setup & shipping',
  'content.manage'=>'Content & SEO','team.manage'=>'Team roles & access','audit.view'=>'Activity history',
 ];}
}
