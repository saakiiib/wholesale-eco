<?php
namespace App\Models;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class User extends Authenticatable {
 use Notifiable;
 protected $fillable=['name','email','password','role','business_id','admin_role_id'];
 protected $hidden=['password','remember_token'];
 protected function casts():array{return ['email_verified_at'=>'datetime','password'=>'hashed'];}
 public function business(){return $this->belongsTo(Business::class);}
 public function isAdmin():bool{return $this->role==='admin'||($this->role==='staff'&&$this->admin_role_id!==null);}
 public function adminRole(){return $this->belongsTo(AdminRole::class,'admin_role_id');}
 public function hasAdminPermission(string $permission):bool {
  if($this->role==='admin')return true; // legacy full admins remain superadmins
  if($this->role!=='staff')return false;
  return in_array($permission,$this->adminRole?->permissions??[],true);
 }
}
