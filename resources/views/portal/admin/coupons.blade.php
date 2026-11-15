@extends('portal.shell',['portal'=>'admin'])
@section('title','Discount codes')
@section('portalContent')
@include('portal.admin._head',['eyebrow'=>'PROMOTIONS','heading'=>'Coupons & discount codes','subtitle'=>'Create fixed amount or percentage promotions; all discounts are recalculated during server-side checkout.'])
<div class="portal-two"><section class="portal-card"><h2>Existing discounts</h2><div class="portal-tablewrap"><table class="portal-table"><thead><tr><th>Code</th><th>Discount</th><th>Used / reserved</th><th>Validity</th></tr></thead><tbody>
@forelse($coupons as $c)<tr><td><strong>{{$c->code}}</strong><small>{{$c->active?'Enabled':'Disabled'}}</small></td><td>{{$c->discount_type==='fixed'?'£'.number_format($c->discount_value/100,2):number_format($c->discount_value/100,2).'%'}} </td><td>{{$c->redeemed_count}} / {{$c->reserved_count}} · Limit {{$c->max_uses??'unlimited'}}</td><td>{{optional($c->expires_at)->format('d M Y')??'No expiry'}}</td></tr>@empty<tr><td colspan="4">No coupons created.</td></tr>@endforelse</tbody></table></div>{{$coupons->links()}}
@foreach($coupons as $c)
<details style="margin:12px 0;padding:12px;border:1px solid #dce9dd;border-radius:9px"><summary style="cursor:pointer;font-weight:700">Edit {{$c->code}}</summary>
<form class="portal-form" method="post" action="{{route('admin.coupons.update',$c)}}">@csrf @method('PUT')
<label>Code<input name="code" value="{{$c->code}}" required></label>
<label>Discount<select name="discount_type"><option value="fixed" @selected($c->discount_type==='fixed')>Fixed (£)</option><option value="percent" @selected($c->discount_type==='percent')>Percentage (%)</option></select></label>
<label>Amount<input name="amount" type="number" min="0.01" step="0.01" value="{{number_format($c->discount_value/100,2,'.','')}}" required></label>
<label>Minimum order ex VAT (pence)<input name="minimum_subtotal_pence" type="number" min="0" value="{{$c->minimum_subtotal_pence}}" required></label>
<label>Maximum saving (pence)<input name="max_discount_pence" type="number" min="1" value="{{$c->max_discount_pence}}"></label>
<label>Max paid redemptions (1 = single use)<input name="max_uses" type="number" min="1" value="{{$c->max_uses}}"></label>
<label>Starts at<input type="datetime-local" name="starts_at" value="{{$c->starts_at?->format('Y-m-d\TH:i')}}"></label>
<label>Expires at<input type="datetime-local" name="expires_at" value="{{$c->expires_at?->format('Y-m-d\TH:i')}}"></label>
<label class="portal-check"><input type="checkbox" name="active" value="1" @checked($c->active)> Active</label>
<button class="btn">Save coupon</button></form></details>
@endforeach</section>
<section class="portal-card"><h2>Create coupon</h2><form method="post" action="{{route('admin.coupons.store')}}" class="portal-form">@csrf
<label>Code<input name="code" required placeholder="WELCOME10" pattern="[A-Za-z0-9_-]{3,60}"></label>
<label>Discount type<select name="discount_type"><option value="percent">Percentage</option><option value="fixed">Fixed amount (£)</option></select></label>
<label>Amount (enter 10 for 10% or £10, depending on type)<input name="amount" type="number" min="0.01" step="0.01" required></label>
<label>Minimum basket ex VAT (pence)<input type="number" name="minimum_subtotal_pence" value="0" min="0" required></label>
<label>Maximum discount in pence (optional)<input type="number" name="max_discount_pence" min="1"></label>
<label>Maximum uses (1 = one-off, blank = unlimited)<input type="number" name="max_uses" min="1"></label>
<label>Starts at (optional)<input type="datetime-local" name="starts_at"></label>
<label>Expires at (optional)<input type="datetime-local" name="expires_at"></label>
<label class="portal-check"><input type="checkbox" name="active" value="1" checked> Enabled</label>
<button class="btn">Create coupon</button></form>
<p><small>Discounts apply to product subtotals before VAT. Single-use coupons can be set to a maximum of 1 paid redemption. Existing coupons can be edited below. Coupon refunds and reservation recovery need staging testing.</small></p></section></div>
@endsection
