@extends('layouts.app')
@section('title',($page->meta_title ?: $page->title).' | Wholesale Eco Products')
@section('meta_description',$page->meta_description ?: $page->title)
@section('content')
<article style="max-width:850px;margin:50px auto;line-height:1.85"><h1>{{ $page->title }}</h1><div style="white-space:pre-line">{{ $page->body }}</div></article>
@endsection
