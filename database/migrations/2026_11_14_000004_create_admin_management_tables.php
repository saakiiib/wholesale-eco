<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  Schema::create('store_settings',function(Blueprint $t){$t->id();$t->string('key',120)->unique();$t->text('value')->nullable();$t->timestamps();});
  Schema::create('cms_pages',function(Blueprint $t){$t->id();$t->string('title',180);$t->string('slug',180)->unique();$t->longText('body')->nullable();$t->string('meta_title',180)->nullable();$t->string('meta_description',320)->nullable();$t->boolean('published')->default(false);$t->timestamps();});
  Schema::create('shipping_zones',function(Blueprint $t){$t->id();$t->string('name',140);$t->string('postcode_prefixes',500)->nullable();$t->unsignedInteger('base_rate_pence')->default(0);$t->unsignedInteger('free_above_pence')->nullable();$t->boolean('enabled')->default(true);$t->unsignedInteger('priority')->default(100);$t->timestamps();});
  Schema::table('products',function(Blueprint $t){$t->string('meta_title',180)->nullable();$t->string('meta_description',320)->nullable();});
  Schema::table('categories',function(Blueprint $t){$t->text('description')->nullable();$t->string('meta_title',180)->nullable();$t->string('meta_description',320)->nullable();});
 }
 public function down():void {
  Schema::table('categories',fn(Blueprint $t)=>$t->dropColumn(['description','meta_title','meta_description']));
  Schema::table('products',fn(Blueprint $t)=>$t->dropColumn(['meta_title','meta_description']));
  Schema::dropIfExists('shipping_zones');Schema::dropIfExists('cms_pages');Schema::dropIfExists('store_settings');
 }
};
