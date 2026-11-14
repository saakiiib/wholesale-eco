<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('businesses',function(Blueprint $t){$t->id();$t->string('name');$t->string('trading_name')->nullable();$t->string('vat_number')->nullable();$t->string('phone')->nullable();$t->enum('status',['active','suspended'])->default('active');$t->timestamps();});
  Schema::create('users',function(Blueprint $t){$t->id();$t->foreignId('business_id')->nullable()->constrained()->nullOnDelete();$t->string('name');$t->string('email')->unique();$t->timestamp('email_verified_at')->nullable();$t->string('password');$t->enum('role',['customer','staff','admin'])->default('customer');$t->rememberToken();$t->timestamps();});
  Schema::create('password_reset_tokens',function(Blueprint $t){$t->string('email')->primary();$t->string('token');$t->timestamp('created_at')->nullable();});
  Schema::create('sessions',function(Blueprint $t){$t->string('id')->primary();$t->foreignId('user_id')->nullable()->index();$t->string('ip_address',45)->nullable();$t->text('user_agent')->nullable();$t->longText('payload');$t->integer('last_activity')->index();});
  Schema::create('addresses',function(Blueprint $t){$t->id();$t->foreignId('business_id')->constrained()->cascadeOnDelete();$t->string('label')->default('Main');$t->string('contact_name')->nullable();$t->string('line1');$t->string('line2')->nullable();$t->string('city');$t->string('county')->nullable();$t->string('postcode',20);$t->string('country',2)->default('GB');$t->boolean('is_default')->default(false);$t->timestamps();});
  Schema::create('categories',function(Blueprint $t){$t->id();$t->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();$t->string('name');$t->string('slug')->unique();$t->boolean('active')->default(true);$t->integer('sort_order')->default(0);$t->timestamps();});
  Schema::create('products',function(Blueprint $t){$t->id();$t->foreignId('category_id')->nullable()->constrained()->nullOnDelete();$t->string('name');$t->string('slug')->unique();$t->text('description')->nullable();$t->string('image_path')->nullable();$t->boolean('active')->default(true);$t->timestamps();});
  Schema::create('product_variants',function(Blueprint $t){$t->id();$t->foreignId('product_id')->constrained()->cascadeOnDelete();$t->string('sku')->unique();$t->string('label');$t->json('attributes')->nullable();$t->unsignedInteger('units_per_pack')->default(1);$t->unsignedInteger('packs_per_pallet')->nullable();$t->unsignedInteger('base_pack_price_pence');$t->unsignedInteger('vat_rate_bps')->default(2000);$t->unsignedInteger('stock_packs')->default(0);$t->boolean('active')->default(true);$t->timestamps();});
  Schema::create('price_tiers',function(Blueprint $t){$t->id();$t->foreignId('product_variant_id')->constrained()->cascadeOnDelete();$t->unsignedInteger('min_packs');$t->unsignedInteger('unit_price_pence');$t->unique(['product_variant_id','min_packs']);$t->timestamps();});
  Schema::create('price_groups',function(Blueprint $t){$t->id();$t->string('name')->unique();$t->timestamps();});
  Schema::create('customer_prices',function(Blueprint $t){$t->id();$t->foreignId('business_id')->constrained()->cascadeOnDelete();$t->foreignId('product_variant_id')->constrained()->cascadeOnDelete();$t->unsignedInteger('pack_price_pence');$t->unique(['business_id','product_variant_id']);$t->timestamps();});
 }
 public function down():void{foreach(['customer_prices','price_groups','price_tiers','product_variants','products','categories','addresses','sessions','password_reset_tokens','users','businesses'] as $table)Schema::dropIfExists($table);}
};
