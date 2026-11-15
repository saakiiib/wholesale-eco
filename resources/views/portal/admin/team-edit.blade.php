@extends('portal.shell',['portal'=>'admin'])
@section('title','Edit admin account')
@section('portalContent')
@include('portal.admin._head',['eyebrow'=>'ADMIN USER MANAGEMENT','heading'=>'Edit administrator','subtitle'=>$user->email,'back'=>route('admin.team.index')])
<section class="portal-card portal-form-width"><form class="portal-form" method="post" action="{{route('admin.team.profile.update',$user)}}">@csrf @method('PUT')
<label>Full name<input name="name" value="{{old('name',$user->name)}}" required></label><label>Email<input type="email" name="email" value="{{old('email',$user->email)}}" required></label><label>Contact number<input name="phone" value="{{old('phone',$user->phone)}}"></label>
<hr><p>Leave password blank to keep the current password. For security, share a new password only through a secure channel.</p>
<label>Current password (required when changing your own password)<input name="current_password" type="password" autocomplete="current-password"></label><label>New password<input type="password" name="password" minlength="12" autocomplete="new-password"></label><label>Confirm new password<input type="password" name="password_confirmation" autocomplete="new-password"></label><button class="btn">Save account</button></form></section>
@endsection
