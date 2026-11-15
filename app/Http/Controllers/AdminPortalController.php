<?php
namespace App\Http\Controllers;
use App\Models\{Business,Category,Order,Product,ProductVariant,Address};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
class AdminPortalController {
 public function dashboard(){return view('portal.admin.dashboard',[
  'orders'=>Order::latest()->take(8)->get(), 'revenue'=>Order::where('payment_status','paid')->sum('grand_total_pence'),
  'ordersCount'=>Order::count(), 'businessCount'=>Business::where('status','active')->count(),
  'lowStock'=>ProductVariant::with('product')->where('stock_packs','<=',(int)\App\Models\StoreSetting::getValue('low_stock_threshold','10'))->orderBy('stock_packs')->take(7)->get(),
  'productsCount'=>Product::count(),
  'lowStockCount'=>ProductVariant::where('stock_packs','<=',(int)\App\Models\StoreSetting::getValue('low_stock_threshold','10'))->count(),
  'openOrders'=>Order::whereIn('status',['paid','processing'])->count(),
  'monthlySales'=>Order::where('payment_status','paid')->where('paid_at','>=',now()->startOfMonth())->sum('grand_total_pence'),
  'pipeline'=>Order::selectRaw('status,COUNT(*) as total')->groupBy('status')->pluck('total','status'),
  'chartDays'=>collect(range(6,0))->map(fn($n)=>now()->subDays($n)->format('D'))->all(),
  'chartValues'=>collect(range(6,0))->map(fn($n)=>(int)Order::where('payment_status','paid')->whereDate('paid_at',now()->subDays($n)->toDateString())->sum('grand_total_pence'))->all(),
  // V7 dashboard widgets use actual database records; no illustration-only figures.
  'topSelling'=>DB::table('order_items')->join('orders','orders.id','=','order_items.order_id')
     ->where('orders.payment_status','paid')->selectRaw('order_items.sku,order_items.product_name,SUM(order_items.packs) as packs,SUM(order_items.line_subtotal_pence) as revenue_pence')
     ->groupBy('order_items.sku','order_items.product_name')->orderByDesc('revenue_pence')->limit(5)->get(),
  'shipmentBreakdown'=>Order::where('payment_status','paid')->selectRaw('status,COUNT(*) as total')->groupBy('status')->pluck('total','status'),
  'newCustomers30d'=>Business::where('created_at','>=',now()->subDays(30))->count(),
  'activeCustomers30d'=>Business::whereHas('orders',fn($q)=>$q->where('payment_status','paid')->where('created_at','>=',now()->subDays(30)))->count()
 ]);}
 public function products(Request $r){$q=Product::with('category')->withCount('variants')->orderBy('category_id')->orderBy('sort_order')->orderBy('name');if($r->filled('q'))$q->where('name','like','%'.$r->query('q').'%');return view('portal.admin.products',['products'=>$q->paginate(16)->withQueryString(),'categories'=>Category::orderBy('name')->get()]);}
 public function createProduct(){return view('portal.admin.product-form',['product'=>null,'categories'=>Category::orderBy('name')->get()]);}
 public function productForm(?Product $product=null){return view('portal.admin.product-form',['product'=>$product,'categories'=>Category::orderBy('name')->get()]);}
 public function editProduct(Product $product){return $this->productForm($product->load('variants'));}
 private function productData(Request $r,?Product $product=null):array {return $r->validate([
  'name'=>'required|string|max:200','slug'=>['nullable','alpha_dash','max:200',Rule::unique('products','slug')->ignore($product?->id)],
  'description'=>'nullable|string|max:10000','category_id'=>'nullable|exists:categories,id','image_path'=>'nullable|string|max:500','meta_title'=>'nullable|string|max:180','meta_description'=>'nullable|string|max:320','sort_order'=>'nullable|integer|min:0|max:999999','active'=>'sometimes|boolean']);}
 public function storeProduct(Request $r){$data=$this->productData($r);$slug=$data['slug']??Str::slug($data['name']);if(!$slug || Product::where('slug',$slug)->exists())return back()->withErrors(['slug'=>'Please choose a unique product slug.'])->withInput();$data['slug']=$slug;$data['active']=$r->boolean('active');$newProduct=Product::create($data);return redirect()->route('admin.workspace.show',$newProduct)->with('success','Product created. Finish variants, prices, images and SEO in this workspace.');}
 public function updateProduct(Request $r,Product $product){$data=$this->productData($r,$product);if(empty($data['slug']))unset($data['slug']);$data['active']=$r->boolean('active');$product->update($data);return back()->with('success','Product updated.');}
 public function variants(Product $product){return view('portal.admin.product-variants',['product'=>$product->load('variants.priceTiers')]);}
 public function createVariant(Request $r,Product $product){$data=$r->validate(['sku'=>'required|string|max:100|unique:product_variants,sku','label'=>'required|string|max:180','units_per_pack'=>'required|integer|min:1|max:1000000','packs_per_pallet'=>'nullable|integer|min:1|max:100000','base_pack_price_pence'=>'required|integer|min:1|max:100000000','vat_rate_bps'=>'required|integer|in:0,500,2000','stock_packs'=>'required|integer|min:0|max:10000000','capacity_ml'=>'nullable|integer|min:1|max:100000','length_mm'=>'nullable|integer|min:1|max:100000','width_mm'=>'nullable|integer|min:1|max:100000','height_mm'=>'nullable|integer|min:1|max:100000','package_type'=>'nullable|string|max:100','material'=>'nullable|string|max:100','allow_pallet_orders'=>'sometimes|boolean','show_unit_prices'=>'sometimes|boolean','show_pallet_units'=>'sometimes|boolean']);$data['attributes']=array_filter(['capacity_ml'=>$data['capacity_ml']??null,'length_mm'=>$data['length_mm']??null,'width_mm'=>$data['width_mm']??null,'height_mm'=>$data['height_mm']??null,'type'=>$data['package_type']??null,'material'=>$data['material']??null],fn($v)=>$v!==null && $v!=='');foreach(['capacity_ml','length_mm','width_mm','height_mm','package_type','material'] as $field)unset($data[$field]);$data['allow_pallet_orders']=$r->boolean('allow_pallet_orders') && !empty($data['packs_per_pallet']);$data['show_unit_prices']=$r->boolean('show_unit_prices');$data['show_pallet_units']=$r->boolean('show_pallet_units');$data['active']=true;$product->variants()->create($data);return back()->with('success','Variant created.');}
 public function editVariant(Request $r,ProductVariant $variant){$data=$r->validate(['label'=>'required|string|max:180','units_per_pack'=>'required|integer|min:1|max:1000000','packs_per_pallet'=>'nullable|integer|min:1|max:100000','base_pack_price_pence'=>'required|integer|min:1|max:100000000','vat_rate_bps'=>'required|integer|in:0,500,2000','active'=>'sometimes|boolean','capacity_ml'=>'nullable|integer|min:1|max:100000','length_mm'=>'nullable|integer|min:1|max:100000','width_mm'=>'nullable|integer|min:1|max:100000','height_mm'=>'nullable|integer|min:1|max:100000','package_type'=>'nullable|string|max:100','material'=>'nullable|string|max:100','allow_pallet_orders'=>'sometimes|boolean','show_unit_prices'=>'sometimes|boolean','show_pallet_units'=>'sometimes|boolean']);$data['attributes']=array_filter(['capacity_ml'=>$data['capacity_ml']??null,'length_mm'=>$data['length_mm']??null,'width_mm'=>$data['width_mm']??null,'height_mm'=>$data['height_mm']??null,'type'=>$data['package_type']??null,'material'=>$data['material']??null],fn($v)=>$v!==null && $v!=='');foreach(['capacity_ml','length_mm','width_mm','height_mm','package_type','material'] as $field)unset($data[$field]);$data['allow_pallet_orders']=$r->boolean('allow_pallet_orders') && !empty($data['packs_per_pallet']);$data['show_unit_prices']=$r->boolean('show_unit_prices');$data['show_pallet_units']=$r->boolean('show_pallet_units');$data['active']=$r->boolean('active');$data['attributes']=array_merge($variant->attributes??[],$data['attributes']);$variant->update($data);return back()->with('success','Variant updated. Stock is changed through audited stock adjustments.');}
 public function categories(){
  $all=Category::withCount('products')->orderBy('sort_order')->orderBy('name')->get();
  $ordered=collect();$visited=[];
  $walk=function($parentId,$depth=0)use(&$walk,$all,&$ordered,&$visited){
   foreach($all->filter(fn($c)=>$c->parent_id===$parentId) as $category){
    if(isset($visited[$category->id]))continue;
    $visited[$category->id]=true;$category->admin_depth=min(10,$depth);$ordered->push($category);
    $walk($category->id,$depth+1);
   }
  };
  $walk(null);
  foreach($all as $category){if(!isset($visited[$category->id])){$category->admin_depth=0;$ordered->push($category);}}
  return view('portal.admin.categories',['categories'=>$ordered]);
 }
 public function storeCategory(Request $r){$data=$r->validate(['name'=>'required|string|max:150','slug'=>'required|alpha_dash|max:150|unique:categories,slug','parent_id'=>'nullable|exists:categories,id','sort_order'=>'nullable|integer|min:0|max:999999']);$data['active']=true;Category::create($data);return back()->with('success','Category created.');}
 public function moveCategory(Request $r,Category $category){
  $d=$r->validate(['direction'=>'required|in:up,down']);
  DB::transaction(function()use($category,$d){
   $siblings=Category::where('parent_id',$category->parent_id)->orderBy('sort_order')->orderBy('name')->lockForUpdate()->get();
   $ids=$siblings->pluck('id')->all();$index=array_search($category->id,$ids,true);$other=$index+($d['direction']==='up'?-1:1);
   if($index===false||$other<0||$other>=count($ids))return;
   [$ids[$index],$ids[$other]]=[$ids[$other],$ids[$index]];
   foreach($ids as $position=>$id)Category::whereKey($id)->update(['sort_order'=>($position+1)*10]);
  });return back()->with('success','Category order updated.');
 }
 public function moveProduct(Request $r,Product $product){
  $d=$r->validate(['direction'=>'required|in:up,down']);
  DB::transaction(function()use($product,$d){
   $siblings=Product::where('category_id',$product->category_id)->orderBy('sort_order')->orderBy('name')->lockForUpdate()->get();
   $ids=$siblings->pluck('id')->all();$index=array_search($product->id,$ids,true);$other=$index+($d['direction']==='up'?-1:1);
   if($index===false||$other<0||$other>=count($ids))return;
   [$ids[$index],$ids[$other]]=[$ids[$other],$ids[$index]];
   foreach($ids as $position=>$id)Product::whereKey($id)->update(['sort_order'=>($position+1)*10]);
  });return back()->with('success','Product order updated.');
 }
 public function customers(Request $r){$q=Business::with('users:id,business_id,email')->withCount('orders','users')->latest();if($r->filled('q'))$q->where('name','like','%'.$r->query('q').'%');return view('portal.admin.customers',['businesses'=>$q->paginate(15)->withQueryString()]);}
 public function customer(Business $business){return view('portal.admin.customer',['business'=>$business->load('users','addresses'),'orders'=>$business->orders()->latest()->take(20)->get()]);}
 public function customerStatus(Request $r,Business $business){$data=$r->validate(['status'=>'required|in:active,suspended']);$business->update($data);return back()->with('success','Account status updated.');}
 public function orders(Request $r){$q=Order::latest();if($r->filled('q'))$q->where(function($sub)use($r){$term='%'.$r->query('q').'%';$sub->where('order_number','like',$term)->orWhere('business_name','like',$term);});if(in_array($r->query('payment'),['paid','unpaid','failed'],true))$q->where('payment_status',$r->query('payment'));
  $status=$r->query('status','all');
  $allowed=['pending','paid','processing','packed','dispatched','delivered','collected','returned','cancelled'];
  if(in_array($status,$allowed,true)){
   if($status==='pending')$q->whereIn('status',['pending','pending_payment']);
   else $q->where('status',$status);
  }
  return view('portal.admin.orders',['orders'=>$q->paginate(20)->withQueryString()]);}
 public function order(Order $order){return view('portal.admin.order',['order'=>$order->load('items','business','payment')]);}
 public function reports(){return view('portal.admin.reports',['monthSales'=>Order::where('payment_status','paid')->where('paid_at','>=',now()->startOfMonth())->sum('grand_total_pence'), 'revenue'=>Order::where('payment_status','paid')->sum('grand_total_pence'),'paidOrders'=>Order::where('payment_status','paid')->count(), 'top'=>DB::table('order_items')->join('orders','orders.id','=','order_items.order_id')->where('orders.payment_status','paid')->selectRaw('order_items.sku,order_items.product_name,SUM(order_items.packs) as packs,SUM(order_items.line_subtotal_pence) as total')->groupBy('order_items.sku','order_items.product_name')->orderByDesc('total')->limit(12)->get()]);}
}
