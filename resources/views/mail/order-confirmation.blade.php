@php
    $address = $order->shipping_address ?? [];
    $contactEmail = $settings['support_email'] ?? $settings['business_email'] ?? null;
    $contactPhone = $settings['business_phone'] ?? null;
    $businessName = $settings['business_name'] ?? 'Wholesale Eco Products';
    $contactBits = array_values(array_filter([$contactEmail, $contactPhone]));
    $contactText = $contactBits ? ' or contact us at '.implode(' / ', $contactBits) : '';
@endphp
<!doctype html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Order {{ $order->order_number }} confirmed</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f0;color:#1e392f;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Arial,sans-serif;font-size:14px;line-height:1.6;">
<div style="max-width:640px;margin:0 auto;padding:24px 16px;">

    <div style="background:#123c33;color:#ffffff;padding:22px 26px;border-radius:10px 10px 0 0;">
        <div style="font-size:19px;font-weight:700;">{{ $businessName }}</div>
        <div style="font-size:12px;opacity:.8;">Wholesale packaging supplies</div>
    </div>

    <div style="background:#ffffff;padding:26px;border:1px solid #dce6dd;border-top:0;">
        <h1 style="margin:0 0 14px;font-size:24px;line-height:1.25;color:#123c33;">Order confirmed</h1>

        <p style="margin:0 0 12px;">Hi {{ $order->contact_name }},</p>
        <p style="margin:0 0 16px;">Thank you for your order. We have received your payment and will prepare it for dispatch.</p>

        <table style="border-collapse:collapse;width:100%;margin:0 0 18px;">
            <tr>
                <td style="padding:4px 0;color:#657970;">Order number</td>
                <td style="padding:4px 0;text-align:right;font-weight:700;">{{ $order->order_number }}</td>
            </tr>
            <tr>
                <td style="padding:4px 0;color:#657970;">Order date</td>
                <td style="padding:4px 0;text-align:right;">{{ $order->created_at?->format('d M Y') }}</td>
            </tr>
            @if($order->paid_at)
            <tr>
                <td style="padding:4px 0;color:#657970;">Payment date</td>
                <td style="padding:4px 0;text-align:right;">{{ $order->paid_at->format('d M Y') }}</td>
            </tr>
            @endif
        </table>

        <table style="border-collapse:collapse;width:100%;margin:0 0 18px;">
            <thead>
                <tr>
                    <th style="text-align:left;background:#e6f0e6;padding:9px;border-bottom:1px solid #dce6dd;">Item</th>
                    <th style="text-align:center;background:#e6f0e6;padding:9px;border-bottom:1px solid #dce6dd;white-space:nowrap;">Cases</th>
                    <th style="text-align:right;background:#e6f0e6;padding:9px;border-bottom:1px solid #dce6dd;white-space:nowrap;">Price / case</th>
                    <th style="text-align:right;background:#e6f0e6;padding:9px;border-bottom:1px solid #dce6dd;white-space:nowrap;">Line total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                <tr>
                    <td style="padding:9px;border-bottom:1px solid #edf2ee;">
                        {{ $item->product_name }}<br>
                        <small style="color:#657970;">{{ $item->variant_label }} · {{ $item->sku }}</small>
                    </td>
                    <td style="padding:9px;border-bottom:1px solid #edf2ee;text-align:center;">{{ $item->packs }}</td>
                    <td style="padding:9px;border-bottom:1px solid #edf2ee;text-align:right;white-space:nowrap;">&pound;{{ number_format($item->unit_pack_price_pence / 100, 2) }}</td>
                    <td style="padding:9px;border-bottom:1px solid #edf2ee;text-align:right;white-space:nowrap;">&pound;{{ number_format($item->line_subtotal_pence / 100, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table style="border-collapse:collapse;width:100%;max-width:340px;margin:0 0 22px auto;">
            <tr>
                <td style="padding:5px 0;color:#657970;">Subtotal</td>
                <td style="padding:5px 0;text-align:right;">&pound;{{ number_format($order->subtotal_pence / 100, 2) }}</td>
            </tr>
            @if($order->discount_pence > 0)
            <tr>
                <td style="padding:5px 0;color:#657970;">Discount</td>
                <td style="padding:5px 0;text-align:right;">&minus;&pound;{{ number_format($order->discount_pence / 100, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td style="padding:5px 0;color:#657970;">Delivery</td>
                <td style="padding:5px 0;text-align:right;">&pound;{{ number_format($order->delivery_pence / 100, 2) }}</td>
            </tr>
            <tr>
                <td style="padding:5px 0;color:#657970;">VAT on goods</td>
                <td style="padding:5px 0;text-align:right;">&pound;{{ number_format($order->vat_pence / 100, 2) }}</td>
            </tr>
            <tr>
                <td style="padding:10px 0 0;border-top:2px solid #1b6b4b;font-weight:700;">Total paid</td>
                <td style="padding:10px 0 0;border-top:2px solid #1b6b4b;text-align:right;font-weight:700;font-size:17px;">&pound;{{ number_format($order->grand_total_pence / 100, 2) }}</td>
            </tr>
        </table>

        <h2 style="margin:0 0 6px;font-size:15px;color:#123c33;">Delivery address</h2>
        <p style="margin:0 0 18px;">
            {{ $order->business_name }}<br>
            {{ $address['line1'] ?? '' }}<br>
            @if(!empty($address['line2'])){{ $address['line2'] }}<br>@endif
            {{ $address['city'] ?? '' }}<br>
            {{ $address['postcode'] ?? '' }}<br>
            {{ $address['country'] ?? 'GB' }}
        </p>

        <p style="margin:0;color:#657970;">
            If you have any questions about this order, reply to this email{{ $contactText }}.
        </p>
    </div>

    <div style="padding:16px 26px;color:#657970;font-size:12px;">
        {{ $businessName }} &middot; All prices in GBP. Please keep this email as your order confirmation.
    </div>

</div>
</body>
</html>
