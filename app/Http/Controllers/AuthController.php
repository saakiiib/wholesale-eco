<?php
namespace App\Http\Controllers;
use App\Models\Business;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class AuthController {
 public function form(){return view('auth.login');}
 public function login(Request $request){$credentials=$request->validate(['email'=>'required|email','password'=>'required|string']);
  if(!Auth::attempt($credentials))return back()->withErrors(['email'=>'Invalid login details'])->onlyInput('email');
  $request->session()->regenerate();return redirect()->intended(auth()->user()->isAdmin()?route('admin.dashboard'):route('account.dashboard'));
 }
 public function registerForm(){return view('auth.register');}
 public function register(Request $request){
  $data=$request->validate(['business_name'=>'required|string|max:200','name'=>'required|string|max:150','email'=>'required|email|max:200|unique:users,email','password'=>'required|string|min:12|confirmed']);
  $user=DB::transaction(function()use($data){$business=Business::create(['name'=>$data['business_name']]);return User::create(['business_id'=>$business->id,'name'=>$data['name'],'email'=>$data['email'],'password'=>$data['password'],'role'=>'customer']);});
  Auth::login($user);$request->session()->regenerate();return redirect()->route('account.dashboard');
 }
 public function logout(Request $request){Auth::logout();$request->session()->invalidate();$request->session()->regenerateToken();return redirect()->route('home');}
}
