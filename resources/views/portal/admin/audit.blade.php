@extends('portal.shell',['portal'=>'admin'])
@section('title','Activity history')
@section('portalContent')
@include('portal.admin._head',['eyebrow'=>'SECURITY & AUDIT','heading'=>'Activity history','subtitle'=>'Review changes made by authorised team members.'])
<section class="portal-card"><div class="portal-tablewrap"><table class="portal-table"><thead><tr><th>When</th><th>Who</th><th>Action</th><th>Record</th></tr></thead><tbody>@forelse($logs as $log)<tr><td>{{$log->created_at}}</td><td>{{$log->actor??'System'}}</td><td>{{$log->action}}</td><td>{{$log->subject_type}} #{{$log->subject_id}}</td></tr>@empty<tr><td colspan="4">No recorded changes yet.</td></tr>@endforelse</tbody></table></div>{{$logs->links()}}</section>
@endsection
