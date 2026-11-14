<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {Schema::table('product_variants',function(Blueprint $table){
  $table->boolean('allow_pallet_orders')->default(false);
  $table->boolean('show_unit_prices')->default(true);
  $table->boolean('show_pallet_units')->default(true);
 });}
 public function down(): void {Schema::table('product_variants',function(Blueprint $table){$table->dropColumn(['allow_pallet_orders','show_unit_prices','show_pallet_units']);});}
};
