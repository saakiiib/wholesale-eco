<?php
namespace Database\Seeders;
use App\Models\{Address,Business,User};
use Illuminate\Database\Seeder;

class DemoUsersSeeder extends Seeder {
 public function run():void {
  // DEMO ONLY — never run this seeder in production.
  // Passwords rely on the User model's 'hashed' cast.
  User::updateOrCreate(['email'=>'admin@example.com'],[
   'name'=>'Demo Admin',
   'password'=>'admin-demo-password',
   'role'=>'admin','business_id'=>null,'email_verified_at'=>now()]);
  $userPass='demo-password';
  $this->demoBusiness('Riverside Café Ltd','riverside@example.test','12 Boat Lane','Bristol','BS1 4TZ',$userPass);
  $this->demoBusiness('Green Events Catering','events@example.test','88 Festival Road','Manchester','M1 1AA',$userPass);
  // Suspended trade account to demo the admin customer-status toggle; it cannot log in while suspended.
  $this->demoBusiness('Closed Shop Ltd','suspended@example.test','1 Shutters Row','Leeds','LS1 1AA',$userPass,'suspended');
 }
 private function demoBusiness(string $name,string $email,string $line1,string $city,string $postcode,string $password,string $status='active'):void {
  $business=Business::updateOrCreate(['name'=>$name],['trading_name'=>$name,'status'=>$status,
   'phone'=>'020 7946 0000','vat_number'=>'GB'.substr(preg_replace('/\D/','',md5($name)),0,9)]);
  Address::updateOrCreate(['business_id'=>$business->id],['label'=>'Main','contact_name'=>'Demo Contact',
   'line1'=>$line1,'city'=>$city,'postcode'=>$postcode,'country'=>'GB','is_default'=>true]);
  User::updateOrCreate(['email'=>$email],[
   'name'=>strtok($name,' ').' Demo','email'=>$email,'password'=>$password,
   'role'=>'customer','business_id'=>$business->id,'email_verified_at'=>now()]);
 }
}