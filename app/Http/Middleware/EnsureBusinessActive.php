<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class EnsureBusinessActive {
 public function handle(Request $request,Closure $next){
  $user=$request->user();
  if($user && !$user->isAdmin() && $user->business_id){
   abort_unless($user->business && $user->business->status==='active',403,'This trade account is suspended. Contact support.');
  }
  return $next($request);
 }
}
