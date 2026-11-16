<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
  Schema::table('orders',function(Blueprint $table){
   $table->timestamp('dispatch_email_sent_at')->nullable();
   $table->string('dispatch_email_status',20)->nullable();
   $table->timestamp('dispatch_email_claimed_at')->nullable();
  });
 }
 public function down():void {Schema::table('orders',function(Blueprint $table){$table->dropColumn(['dispatch_email_sent_at','dispatch_email_status','dispatch_email_claimed_at']);});}
};
