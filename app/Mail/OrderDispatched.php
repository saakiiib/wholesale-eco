<?php
namespace App\Mail;

use App\Models\Order;
use App\Models\StoreSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderDispatched extends Mailable {
 use Queueable, SerializesModels;
 public function __construct(public Order $order, public ?string $carrier=null, public ?string $trackingNumber=null) {
  $this->order->loadMissing('items');
 }
 public function envelope():Envelope {
  return new Envelope(subject: '[Wholesale Eco] Your order '.$this->order->order_number.' has been dispatched');
 }
 public function content():Content {
  return new Content(view:'mail.order-dispatched',with:[
   'order'=>$this->order,
   'carrier'=>$this->carrier,
   'trackingNumber'=>$this->trackingNumber,
   'settings'=>StoreSetting::whereIn('key',['business_name','business_email','support_email','business_phone','business_address'])->pluck('value','key'),
  ]);
 }
 public function attachments():array{return [];}
}
