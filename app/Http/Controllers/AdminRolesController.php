<?php
namespace App\Http\Controllers;
use App\Models\{AdminRole,User};
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
class AdminRolesController {
 public function index(){abort_unless(auth()->user()?->role==='admin',403);return view('portal.admin.roles',['roles'=>AdminRole::withCount('users')->orderBy('name')->get(),'labels'=>AdminRole::permissionLabels()]);}
 public function save(Request $request,?AdminRole $role=null){abort_unless($request->user()->role==='admin',403);
  $data=$request->validate(['name'=>'required|string|max:100','permissions'=>'nullable|array','permissions.*'=>['string',Rule::in(array_keys(AdminRole::permissionLabels()))]]);
  $values=array_values(array_unique($data['permissions']??[]));
  if($role){$role->update(['name'=>$data['name'],'permissions'=>$values]);$message='Role updated.';}
  else {$role=AdminRole::create(['name'=>$data['name'],'slug'=>Str::slug($data['name']).'-'.Str::lower(Str::random(5)),'permissions'=>$values]);$message='Role created.';}
  return redirect()->route('admin.roles.index')->with('success',$message);
 }
 public function assign(Request $request,User $user){abort_unless($request->user()->role==='admin',403);
  abort_unless($user->role==='staff',422,'Only staff can be assigned a restricted role; superadmins are managed separately.');
  $data=$request->validate(['admin_role_id'=>'required|exists:admin_roles,id']);
  $user->update(['admin_role_id'=>$data['admin_role_id']]);
  return back()->with('success','Staff permissions updated.');
 }
}
