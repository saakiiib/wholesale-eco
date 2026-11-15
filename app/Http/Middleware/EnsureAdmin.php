<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class EnsureAdmin {
 public function handle(Request $request,Closure $next){
  $user=$request->user();abort_unless($user?->isAdmin(),403);
  $name=(string)($request->route()?->getName()??'');
  // Server-side allowlist: new or unrecognised routes default to superadmin-only.
  $map=[
    'admin.dashboard'=>'dashboard.view','admin.reports'=>'dashboard.view',
    'admin.orders.'=>'orders.manage','admin.orders-export'=>'orders.manage',
    'admin.products.'=>'catalogue.manage','admin.categories.'=>'catalogue.manage','admin.images.'=>'catalogue.manage',
    'admin.inventory.'=>'inventory.manage','admin.variants'=>'inventory.manage','admin.stock'=>'inventory.manage','admin.tier'=>'inventory.manage',
    'admin.customers.'=>'customers.manage',
    'admin.settings.'=>'settings.manage','admin.shipping.'=>'settings.manage',
    'admin.content.'=>'content.manage','admin.seo.'=>'content.manage',
    'admin.team.'=>'team.manage','admin.roles.'=>'team.manage',
    'admin.audit.'=>'audit.view',
  ];
  if($user->role!=='admin'){
   $needed=null;
   foreach($map as $prefix=>$permission){if($name===$prefix||str_starts_with($name,$prefix)){$needed=$permission;break;}}
   abort_unless($needed&&$user->hasAdminPermission($needed),403);
  }
  return $next($request);
 }
}
