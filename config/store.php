<?php
return [
 'currency'=>'gbp',
 'delivery_fee_pence'=>(int) env('STORE_DELIVERY_FEE_PENCE',995),
 'free_delivery_pence'=>(int) env('STORE_FREE_DELIVERY_PENCE',15000),
 'default_vat_bps'=>(int) env('STORE_DEFAULT_VAT_BPS',2000),
 'stripe_secret'=>env('STRIPE_SECRET'),
 'stripe_webhook_secret'=>env('STRIPE_WEBHOOK_SECRET'),
];
