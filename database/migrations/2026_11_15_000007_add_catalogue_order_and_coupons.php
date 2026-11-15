<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
 public function up():void {
  Schema::table('products',fn(Blueprint $t)=>$t->unsignedInteger('sort_order')->default(100)->index());
  foreach(DB::table('categories')->orderBy('sort_order')->orderBy('name')->pluck('id') as $i=>$id)DB::table('categories')->where('id',$id)->update(['sort_order'=>($i+1)*10]);
  foreach(DB::table('products')->orderBy('name')->pluck('id') as $i=>$id)DB::table('products')->where('id',$id)->update(['sort_order'=>($i+1)*10]);
  Schema::create('coupons',function(Blueprint $t){
   $t->id();$t->string('code',60)->unique();$t->enum('discount_type',['fixed','percent']);$t->unsignedInteger('discount_value');$t->unsignedInteger('minimum_subtotal_pence')->default(0);$t->unsignedInteger('max_discount_pence')->nullable();$t->unsignedInteger('max_uses')->nullable();$t->unsignedInteger('redeemed_count')->default(0);$t->unsignedInteger('reserved_count')->default(0);$t->timestamp('starts_at')->nullable();$t->timestamp('expires_at')->nullable();$t->boolean('active')->default(true);$t->timestamps();
  });
  Schema::table('orders',function(Blueprint $t){$t->foreignId('coupon_id')->nullable()->constrained('coupons')->nullOnDelete();$t->unsignedInteger('discount_pence')->default(0);});
 }
 public function down():void{Schema::table('orders',fn(Blueprint $t)=>$t->dropConstrainedForeignId('coupon_id'));Schema::dropIfExists('coupons');Schema::table('products',fn(Blueprint $t)=>$t->dropColumn('sort_order'));}
};
