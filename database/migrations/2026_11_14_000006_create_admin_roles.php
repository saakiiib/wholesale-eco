<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  Schema::create('admin_roles',function(Blueprint $table){$table->id();$table->string('name',100);$table->string('slug',100)->unique();$table->json('permissions');$table->timestamps();});
  Schema::table('users',function(Blueprint $table){$table->foreignId('admin_role_id')->nullable()->constrained('admin_roles')->nullOnDelete();});
 }
 public function down():void {Schema::table('users',fn(Blueprint $table)=>$table->dropConstrainedForeignId('admin_role_id'));Schema::dropIfExists('admin_roles');}
};
