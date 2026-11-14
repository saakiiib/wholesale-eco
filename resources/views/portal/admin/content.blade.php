@extends('portal.shell',['portal'=>'admin'])
@section('title','Content management')
@section('portalContent')
<div class="portal-heading"><div><span class="portal-eyebrow">CONTENT MANAGEMENT</span><h1>Website pages</h1><p>Create business information, delivery, policy and help pages.</p></div><a href="{{route('admin.content.create')}}" class="btn">+ New page</a></div>
<section class="portal-card"><div class="portal-tablewrap"><table class="portal-table"><thead><tr><th>Page</th><th>URL</th><th>Status</th><th>Updated</th><th></th></tr></thead><tbody>@forelse($pages as $page)<tr><td><strong>{{$page->title}}</strong></td><td>/pages/{{$page->slug}}</td><td><span class="portal-pill">{{$page->published?'Published':'Draft'}}</span></td><td>{{$page->updated_at->format('d M Y')}}</td><td><a href="{{route('admin.content.edit',$page)}}">Edit →</a></td></tr>@empty<tr><td colspan="5">No content pages yet.</td></tr>@endforelse</tbody></table></div>{{$pages->links()}}</section>
@endsection
