<?php
namespace App\Http\Controllers;
use App\Models\{Order,StoreSetting};
class AdminInvoiceController {
 public function show(Order $order){return view('portal.invoice',['order'=>$order->load('items'),'settings'=>StoreSetting::pluck('value','key')]);}
}
