<?php
namespace App\Http\Controllers;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Throwable;
class ContactController {
 public function show(){return view('shop.contact',['contact'=>StoreSetting::whereIn('key',['business_name','business_email','business_phone','business_address','support_email'])->pluck('value','key')]);}
 public function send(Request $request){
  $data=$request->validate(['name'=>'required|string|max:120','email'=>'required|email|max:180','business'=>'nullable|string|max:180','subject'=>'required|string|max:160','message'=>'required|string|min:10|max:4000','website'=>'nullable|size:0']);
  $to=StoreSetting::getValue('support_email')?:StoreSetting::getValue('business_email');
  if(!$to || !filter_var($to,FILTER_VALIDATE_EMAIL) || str_ends_with($to,'@example.test')){return back()->withErrors(['message'=>'Contact email has not been configured. Please use the published business telephone number.'])->withInput();}
  try{Mail::to($to)->send(new \App\Mail\ContactEnquiry($data));}
  catch(Throwable $e){Log::error('Contact form mail failed',['exception'=>get_class($e),'message'=>$e->getMessage()]);return back()->withErrors(['message'=>'Your message could not be sent. Please contact us by telephone or email.'])->withInput();}
  return redirect()->route('contact')->with('success','Thank you — your enquiry has been submitted.');
 }
}
