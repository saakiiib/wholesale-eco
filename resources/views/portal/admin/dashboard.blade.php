@extends('portal.shell',['portal'=>'admin'])
@section('title','Operations overview · Wholesale Eco Products')
@section('portalContent')
<div class="portal-heading"><div><span class="portal-eyebrow">ADMIN DASHBOARD</span><h1>Operations overview</h1><p>Everything important across your wholesale store, at a glance.</p></div><a class="portal-minibtn" href="{{route('catalogue')}}">↗ Visit shop</a></div>
<section class="admin-hero"><div><small>BUSINESS OVERVIEW</small><h2>A clearer view of your wholesale operation.</h2><p>Orders, inventory, trade customers and pricing in one carefully designed workspace.</p></div><a href="{{route('admin.orders.index')}}">Manage today's orders →</a></section>
<div class="portal-kpis"><div><small>Gross sales this month</small><strong>£{{number_format($monthlySales/100,2)}}</strong><span>Paid orders · incl. VAT &amp; delivery</span></div><div><small>Orders received</small><strong>{{$ordersCount}}</strong><span>{{$openOrders}} waiting / processing</span></div><div><small>Active trade customers</small><strong>{{$businessCount}}</strong><span>Registered company accounts</span></div><div><small>Low-stock products</small><strong>{{$lowStockCount}}</strong><span>Below the configured stock threshold</span></div></div>
<div class="portal-two"><section class="portal-card"><div class="portal-card-head"><div><span class="portal-eyebrow">LAST SEVEN DAYS</span><h2>Sales overview</h2></div><a href="{{route('admin.reports')}}">View reports →</a></div>
@php($max=max(1,...$chartValues))
<div class="admin-bars" aria-label="Paid sales for the past seven days">@foreach($chartValues as $i=>$value)<div class="admin-bar-wrap" title="£{{number_format($value/100,2)}}"><span>£{{number_format($value/100,0)}}</span><div class="admin-bar" style="height:{{max(5,round($value/$max*150))}}px"></div><small>{{$chartDays[$i]}}</small></div>@endforeach</div></section>
<section class="portal-card"><div class="portal-card-head"><div><span class="portal-eyebrow">FULFILMENT</span><h2>Order pipeline</h2></div><a href="{{route('admin.orders.index')}}">View orders →</a></div>
@php($maxPipe=max(1,(int)$ordersCount))
@foreach(['pending_payment','paid','processing','dispatched','delivered'] as $status)<div class="admin-pipeline"><div><span>{{ucwords(str_replace('_',' ',$status))}}</span><strong>{{(int)($pipeline[$status]??0)}} orders</strong></div><div class="admin-progress"><div style="width:{{min(100,((int)($pipeline[$status]??0))/$maxPipe*100)}}%"></div></div></div>@endforeach</section></div>
<div class="portal-two"><section class="portal-card"><div class="portal-card-head"><div><span class="portal-eyebrow">LATEST TRANSACTIONS</span><h2>Recent wholesale orders</h2></div><a href="{{route('admin.orders.index')}}">Manage orders →</a></div><div class="portal-tablewrap"><table class="portal-table"><thead><tr><th>Reference</th><th>Customer</th><th>Value</th><th>Status</th></tr></thead><tbody>@forelse($orders as $o)<tr><td><a href="{{route('admin.orders.show',$o)}}">{{$o->order_number}}</a></td><td>{{$o->business_name}}</td><td>£{{number_format($o->grand_total_pence/100,2)}}</td><td><span class="portal-pill">{{str_replace('_',' ',$o->status)}}</span></td></tr>@empty<tr><td colspan="4">No orders received yet.</td></tr>@endforelse</tbody></table></div></section>
<section class="portal-card"><div class="portal-card-head"><div><span class="portal-eyebrow">STOCK ALERTS</span><h2>Inventory alerts</h2></div><a href="{{route('admin.variants')}}">View stock →</a></div>@forelse($lowStock as $v)<div class="portal-line"><span>{{$v->product?->name}}<br><small>{{$v->sku}}</small></span><strong class="admin-low">{{$v->stock_packs}} packs</strong></div>@empty<p>No low-stock variants in the current threshold.</p>@endforelse</section></div>
<div class="portal-two dashboard-v7-grid">
 <section class="portal-card"><div class="portal-card-head"><div><span class="portal-eyebrow">PAID ORDER DATA</span><h2>Top-selling products</h2></div><a href="{{route('admin.reports')}}">Full report →</a></div>
 <div class="portal-tablewrap"><table class="portal-table"><thead><tr><th>Product</th><th>Cases</th><th>Revenue ex VAT</th></tr></thead><tbody>
 @forelse($topSelling as $item)<tr><td>{{$item->product_name}}<br><small>{{$item->sku}}</small></td><td>{{number_format($item->packs)}}</td><td>£{{number_format($item->revenue_pence/100,2)}}</td></tr>
 @empty<tr><td colspan="3">No paid orders yet — sales will appear here automatically.</td></tr>@endforelse
 </tbody></table></div></section>
 <section class="portal-card"><div class="portal-card-head"><div><span class="portal-eyebrow">FULFILMENT INSIGHTS</span><h2>Shipment status</h2></div><a href="{{route('admin.orders.index')}}">View orders →</a></div>
 @php($shippingLabels=['paid'=>'Awaiting processing','processing'=>'Processing','packed'=>'Packed','dispatched'=>'Dispatched','delivered'=>'Delivered'])
 @foreach($shippingLabels as $key=>$label)<div class="portal-line"><span>{{$label}}</span><strong>{{(int)($shipmentBreakdown[$key]??0)}}</strong></div>@endforeach
 <p class="dashboard-v7-note">Counts from paid orders; courier tracking is managed separately.</p></section>
</div>
<div class="portal-two dashboard-v7-grid">
 <section class="portal-card"><div class="portal-card-head"><div><span class="portal-eyebrow">TRADE ACCOUNTS</span><h2>Customer activity</h2></div><a href="{{route('admin.customers.index')}}">View customers →</a></div>
 <div class="dashboard-v7-metrics"><div><strong>{{number_format($businessCount)}}</strong><span>Active accounts</span></div><div><strong>{{number_format($newCustomers30d)}}</strong><span>New accounts (30 days)</span></div><div><strong>{{number_format($activeCustomers30d)}}</strong><span>Purchasing businesses (30 days)</span></div></div>
 </section>
 <section class="portal-card"><div class="portal-card-head"><div><span class="portal-eyebrow">TRACKING STATUS</span><h2>Marketing performance</h2></div><a href="{{route('admin.settings.index')}}">Store settings →</a></div>
 <p><strong>Analytics integration not connected</strong></p><p>Campaign attribution, conversion rates and ad performance require a verified GA4 or marketing integration. No demonstration statistics are shown.</p>
 </section>
</div>
@endsection
