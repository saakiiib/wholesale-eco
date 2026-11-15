<?php
namespace App\Http\Controllers;

use App\Models\{CmsPage,ShippingZone,StoreSetting,User,Order,Product,Category};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminManagementController {
 private function audit(Request $r,string $action,string $type,int $id,array $changes=[]):void {
  DB::table('audit_logs')->insert(['user_id'=>$r->user()->id,'action'=>$action,'subject_type'=>$type,'subject_id'=>$id,'changes'=>json_encode($changes),'created_at'=>now(),'updated_at'=>now()]);
 }
 public function settings(){return view('portal.admin.settings',['settings'=>StoreSetting::pluck('value','key')]);}
 public function updateSettings(Request $r){
  $data=$r->validate([
   'business_name'=>'required|string|max:180','business_email'=>'required|email|max:180','business_phone'=>'nullable|string|max:35',
   'business_address'=>'nullable|string|max:600','vat_number'=>'nullable|string|max:60','company_number'=>'nullable|string|max:60',
   'low_stock_threshold'=>'required|integer|min:0|max:10000','support_email'=>'nullable|email|max:180',
   'default_meta_title'=>'nullable|string|max:180','default_meta_description'=>'nullable|string|max:320',
   'hero_label'=>'nullable|string|max:100','hero_title'=>'nullable|string|max:160','hero_description'=>'nullable|string|max:450',
   'promo_heading'=>'nullable|string|max:180','promo_description'=>'nullable|string|max:600',
   'facebook_url'=>'nullable|url|starts_with:https://|max:500','instagram_url'=>'nullable|url|starts_with:https://|max:500','linkedin_url'=>'nullable|url|starts_with:https://|max:500',
   'whatsapp_number'=>'nullable|regex:/^\+?[0-9 ]{7,20}$/',
  ]);
  DB::transaction(function()use($data,$r){foreach($data as $key=>$value){StoreSetting::updateOrCreate(['key'=>$key],['value'=>$value]);}$this->audit($r,'store.settings.updated','store',1,array_keys($data));});
  return back()->with('success','Business settings saved. These settings do not modify payment credentials.');
 }
 public function shipping(){return view('portal.admin.shipping',['zones'=>ShippingZone::orderBy('priority')->get()]);}
 public function addShipping(Request $r){$d=$r->validate(['name'=>'required|string|max:140','postcode_prefixes'=>'nullable|string|max:500','base_rate_pence'=>'required|integer|min:0|max:1000000','free_above_pence'=>'nullable|integer|min:0|max:100000000','priority'=>'required|integer|min:0|max:9999']);$d['enabled']=true;$zone=ShippingZone::create($d);$this->audit($r,'shipping.zone.created','shipping_zone',$zone->id,$d);return back()->with('success','Shipping zone saved. Activation in checkout is a separate, tested integration step.');}
 public function updateShipping(Request $r,ShippingZone $zone){$d=$r->validate(['name'=>'required|string|max:140','postcode_prefixes'=>'nullable|string|max:500','base_rate_pence'=>'required|integer|min:0|max:1000000','free_above_pence'=>'nullable|integer|min:0|max:100000000','priority'=>'required|integer|min:0|max:9999','enabled'=>'sometimes|boolean']);$d['enabled']=$r->boolean('enabled');$zone->update($d);$this->audit($r,'shipping.zone.updated','shipping_zone',$zone->id,$d);return back()->with('success','Zone updated. Checkout shipping integration remains subject to testing.');}
 public function content(){return view('portal.admin.content',['pages'=>CmsPage::latest()->paginate(20)]);}
 public function editContent(?CmsPage $page=null){return view('portal.admin.content-edit',['page'=>$page]);}
 private function pageData(Request $r,?CmsPage $page=null):array{return $r->validate(['title'=>'required|string|max:180','slug'=>['required','alpha_dash','max:180',Rule::unique('cms_pages','slug')->ignore($page?->id)],'body'=>'nullable|string|max:60000','meta_title'=>'nullable|string|max:180','meta_description'=>'nullable|string|max:320','published'=>'sometimes|boolean']);}
 public function saveContent(Request $r){$d=$this->pageData($r);$d['published']=$r->boolean('published');$page=CmsPage::create($d);$this->audit($r,'cms.created','cms_page',$page->id,['slug'=>$page->slug]);return redirect()->route('admin.content.index')->with('success','Page created. Published pages use /pages/{slug}.');}
 public function updateContent(Request $r,CmsPage $page){$d=$this->pageData($r,$page);$d['published']=$r->boolean('published');$page->update($d);$this->audit($r,'cms.updated','cms_page',$page->id,['slug'=>$page->slug]);return back()->with('success','Page saved.');}
 public function seo(Request $r){$products=Product::orderBy('name')->paginate(20,['*'],'products_page');$categories=Category::orderBy('name')->get();return view('portal.admin.seo',compact('products','categories'));}
 public function saveSeo(Request $r){$d=$r->validate(['type'=>'required|in:product,category','id'=>'required|integer|min:1','meta_title'=>'nullable|string|max:180','meta_description'=>'nullable|string|max:320']);$model=$d['type']==='product'?Product::findOrFail($d['id']):Category::findOrFail($d['id']);$model->update(['meta_title'=>$d['meta_title']??null,'meta_description'=>$d['meta_description']??null]);$this->audit($r,'seo.updated',$d['type'],$model->id,[$d['meta_title']??null]);return back()->with('success','SEO metadata saved.');}
 public function team(){return view('portal.admin.team',['users'=>User::with('adminRole')->whereIn('role',['admin','staff'])->orderBy('name')->paginate(20),'roles'=>\App\Models\AdminRole::orderBy('name')->get()]);}
 public function createTeam(Request $r){abort_unless($r->user()->role==='admin',403);$d=$r->validate(['name'=>'required|string|max:150','email'=>'required|email|max:180|unique:users,email','role'=>'required|in:admin,staff','admin_role_id'=>'nullable|required_if:role,staff|exists:admin_roles,id','password'=>'required|string|min:12|max:100|confirmed']);abort_if($d['role']==='admin' && $r->user()->role!=='admin',403);$d['business_id']=null;$d['admin_role_id']=$d['role']==='staff'?($d['admin_role_id']??null):null;$user=User::create($d);$this->audit($r,'team.created','user',$user->id,['role'=>$user->role]);return back()->with('success','Team member created. Share login credentials using a secure channel.');}
 public function updateTeam(Request $r,User $user){abort_unless(in_array($user->role,['admin','staff'],true),404);abort_unless($r->user()->role==='admin',403);$d=$r->validate(['role'=>'required|in:admin,staff']);abort_if($r->user()->is($user)&&$d['role']!=='admin',422,'You cannot remove your own administrator access.');abort_if($user->role==='admin'&&$d['role']!=='admin'&&User::where('role','admin')->count()<=1,422,'At least one administrator must remain.');$user->update($d);$this->audit($r,'team.role.updated','user',$user->id,$d);return back()->with('success','Team role updated.');}
 public function editTeam(User $user){abort_unless(auth()->user()->role==='admin',403);abort_unless(in_array($user->role,['admin','staff'],true),404);return view('portal.admin.team-edit',compact('user'));}
 public function saveTeamProfile(Request $r,User $user){
  abort_unless($r->user()->role==='admin',403);abort_unless(in_array($user->role,['admin','staff'],true),404);
  $d=$r->validate(['name'=>'required|string|max:150','email'=>['required','email','max:180',Rule::unique('users','email')->ignore($user->id)],'phone'=>'nullable|string|max:40','password'=>'nullable|string|min:12|max:100|confirmed','current_password'=>'nullable|string']);
  $user->name=$d['name'];$user->email=$d['email'];$user->phone=$d['phone']??null;
  if(!empty($d['password'])){if($r->user()->is($user)){abort_unless(!empty($d['current_password'])&&Hash::check($d['current_password'],$r->user()->password),422,'Current password is incorrect.');}$user->password=$d['password'];}$user->save();
  $this->audit($r,'team.profile.updated','user',$user->id,['name'=>$user->name,'email'=>$user->email]);
  return back()->with('success','Administrator account updated.');
 }
 public function auditLog(Request $r){$logs=DB::table('audit_logs')->leftJoin('users','users.id','=','audit_logs.user_id')->select('audit_logs.*','users.name as actor')->orderByDesc('audit_logs.id')->paginate(35);return view('portal.admin.audit',compact('logs'));}
 public function images(Product $product){return view('portal.admin.images',['product'=>$product->load('images')]);}
 public function addImage(Request $r,Product $product){
  $d=$r->validate(['images'=>'required|array|min:1|max:8','images.*'=>'required|image|mimes:jpg,jpeg,png,webp|max:4096','alt_text'=>'nullable|string|max:180']);
  $sort=(int)$product->images()->max('sort_order');
  foreach($r->file('images',[]) as $file){
   $path=$file->store('products','public');
   $img=$product->images()->create(['path'=>$path,'alt_text'=>$d['alt_text']??$product->name,'sort_order'=>++$sort]);
   $this->audit($r,'product.image.added','product_image',$img->id,['product_id'=>$product->id]);
  }
  return back()->with('success',count($r->file('images',[])).' image(s) uploaded.');
 }
 public function moveImage(Request $r,Product $product,\App\Models\ProductImage $image){
  abort_unless((int)$image->product_id===(int)$product->id,404);
  $d=$r->validate(['direction'=>'required|in:up,down']);
  $ordered=$product->images()->orderBy('sort_order')->orderBy('id')->get();
  $idx=$ordered->search(fn($item)=>$item->id===$image->id);
  $other=$d['direction']==='up'?$idx-1:$idx+1;
  if($idx!==false && $other>=0 && $other<$ordered->count()){
   $ordered->splice($other,0,$ordered->splice($idx,1)->all());
   \Illuminate\Support\Facades\DB::transaction(function()use($ordered){foreach($ordered as $i=>$photo){$photo->update(['sort_order'=>$i+1]);}});
   $this->audit($r,'product.image.reordered','product_image',$image->id,['product_id'=>$product->id]);
  }
  return back()->with('success','Gallery order updated.');
 }
 public function removeImage(Request $r,Product $product,\App\Models\ProductImage $image){abort_unless($image->product_id===$product->id,404);$path=$image->path;$this->audit($r,'product.image.removed','product_image',$image->id,['product_id'=>$product->id]);$image->delete();\Illuminate\Support\Facades\Storage::disk('public')->delete($path);return back()->with('success','Image removed.');}
 public function updateCustomer(Request $r,\App\Models\Business $business){$d=$r->validate(['name'=>'required|string|max:200','trading_name'=>'nullable|string|max:200','vat_number'=>'nullable|string|max:60','phone'=>'nullable|string|max:35','status'=>'required|in:active,suspended']);$business->update($d);$this->audit($r,'customer.updated','business',$business->id,array_keys($d));return back()->with('success','Trade customer details updated.');}
 public function editCategory(\App\Models\Category $category){return view('portal.admin.category-edit',['category'=>$category,'categories'=>\App\Models\Category::where('id','!=',$category->id)->orderBy('name')->get()]);}
 public function updateCategory(Request $r,\App\Models\Category $category){$d=$r->validate(['name'=>'required|string|max:150','slug'=>['required','alpha_dash','max:150',Rule::unique('categories','slug')->ignore($category->id)],'parent_id'=>'nullable|exists:categories,id','description'=>'nullable|string|max:10000','meta_title'=>'nullable|string|max:180','meta_description'=>'nullable|string|max:320','sort_order'=>'nullable|integer|min:0|max:999999','active'=>'sometimes|boolean']);abort_if((int)($d['parent_id']??0)===$category->id,422,'Cannot be its own parent.');$parent=$d['parent_id']??null;$seen=[$category->id];while($parent){abort_if(in_array((int)$parent,$seen,true),422,'Circular category hierarchy.');$seen[]=(int)$parent;$parent=\App\Models\Category::whereKey($parent)->value('parent_id');}$d['active']=$r->boolean('active');$category->update($d);$this->audit($r,'category.updated','category',$category->id,array_keys($d));return back()->with('success','Category updated.');}
 public function saveTracking(Request $r,Order $order){
  abort_unless($order->payment_status==='paid',422,'Only paid orders can be dispatched.');
  $d=$r->validate(['carrier'=>'required|string|max:120','tracking_number'=>'required|string|max:180']);
  DB::transaction(function()use($r,$order,$d){
   $order=Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
   abort_unless($order->payment_status==='paid'&&!in_array($order->status,['cancelled','delivered'],true),422,'This order cannot be dispatched.');
   DB::table('shipments')->updateOrInsert(['order_id'=>$order->id],array_merge($d,['dispatched_at'=>now(),'updated_at'=>now(),'created_at'=>now()]));
   $order->update(['status'=>'dispatched']);
   $this->audit($r,'order.dispatched','order',$order->id,$d);
  });
  return back()->with('success','Tracking saved and order marked dispatched.');
 }
 public function exportOrders(){return response()->streamDownload(function(){ $out=fopen('php://output','w');fputcsv($out,['Order','Date','Business','Email','Payment','Fulfilment','Subtotal GBP','VAT GBP','Delivery GBP','Total GBP']);Order::orderBy('id')->chunk(500,function($orders)use($out){foreach($orders as $o){fputcsv($out,[$o->order_number,$o->created_at?->toDateString(),preg_match('/^[=+@\-\t\r]/u',$o->business_name) ? "'".$o->business_name : $o->business_name,preg_match('/^[=+@\-\t\r]/u',$o->email) ? "'".$o->email : $o->email,$o->payment_status,$o->status,number_format($o->subtotal_pence/100,2,'.',''),number_format($o->vat_pence/100,2,'.',''),number_format($o->delivery_pence/100,2,'.',''),number_format($o->grand_total_pence/100,2,'.','')]);}});fclose($out);},'orders-'.now()->format('Ymd').'.csv',['Content-Type'=>'text/csv; charset=UTF-8']);}
}
