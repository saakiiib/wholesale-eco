@extends('portal.shell',['portal'=>'admin'])
@section('title','Editable admin roles')
@section('portalContent')
@include('portal.admin._head',['eyebrow'=>'SECURITY','heading'=>'Roles & permissions','subtitle'=>'Grant the minimum required access. Full administrators always retain all permissions.'])
<div class="portal-two"><section class="portal-card"><h2>Create a role</h2><form method="post" action="{{route('admin.roles.store')}}" class="portal-form">@csrf
<label>Role name<input name="name" required maxlength="100" placeholder="Warehouse manager"></label>
@foreach($labels as $key=>$label)<label class="portal-check"><input type="checkbox" name="permissions[]" value="{{$key}}">{{$label}}</label>@endforeach
<button class="btn">Create role</button></form></section><section class="portal-card"><h2>Manage roles</h2>
@forelse($roles as $role)<form class="portal-form" method="post" action="{{route('admin.roles.update',$role)}}">@csrf @method('PUT')<label>Role name<input name="name" value="{{$role->name}}" required></label><small>{{$role->users_count}} assigned staff members</small><div class="v9-perm-grid">@foreach($labels as $key=>$label)<label class="portal-check"><input type="checkbox" name="permissions[]" value="{{$key}}" @checked(in_array($key,$role->permissions??[],true))>{{$label}}</label>@endforeach</div><button class="portal-minibtn">Save permissions</button></form><hr>@empty<p>No restricted roles exist. Create one to assign to staff accounts.</p>@endforelse
</section></div>
@endsection
