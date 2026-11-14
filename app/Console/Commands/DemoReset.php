<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;

class DemoReset extends Command {
 protected $signature='demo:reset {--force : Skip the confirmation prompt (required outside local/dev/testing)}';
 protected $description='Drop all tables, re-migrate and re-seed demo data (DEMO/DEVELOPMENT ONLY)';
 public function handle():int {
  if(!app()->environment('local','development','testing')&&!$this->option('force')){
   $this->error('demo:reset is destructive and is only allowed in local/development/testing. Pass --force to override.');
   return self::FAILURE;
  }
  if(!$this->option('force')&&!$this->confirm('This will DROP ALL TABLES and re-seed demo data. Continue?',false))return self::SUCCESS;
  $code=$this->call('migrate:fresh',['--seed'=>true,'--force'=>true]);
  if($code!==self::SUCCESS)return $code;
  $this->info('Demo reset complete.');
  $this->table(['Account','Email','Password'],[
   ['Admin',env('DEMO_ADMIN_EMAIL','admin@example.com'),env('DEMO_ADMIN_PASSWORD','admin-demo-password')],
   ['Trade customer','riverside@example.test',env('DEMO_USER_PASSWORD','demo-password')],
   ['Trade customer','events@example.test',env('DEMO_USER_PASSWORD','demo-password')],
   ['Suspended (cannot log in)','suspended@example.test',env('DEMO_USER_PASSWORD','demo-password')]]);
  return self::SUCCESS;
 }
}
