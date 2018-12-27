@auth
	@extends('layout')

	@push('styles')
		<link rel="stylesheet" href="{{asset("/dist/css/module-forum.css")}}"/>
	@endpush
@endauth
@guest
	@extends('layout-owee.layout')

	@section('styles')
		<link rel="stylesheet" href="{{asset("/dist/css/extern.css")}}" type="text/css"/>
		<link rel="stylesheet" href="{{asset("/dist/css/extern-forum.css")}}" type="text/css"/>
	@endsection
@endguest

@section('breadcrumbs')
	@parent
<a href="/forum" title="Forum"><span class="fa fa-wechat module-icon"></span></a>
@endsection


