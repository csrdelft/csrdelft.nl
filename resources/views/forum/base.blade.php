@auth
	@extends('layout')

	@push('styles')
		<link rel="stylesheet" href="{{asset("/dist/css/module-forum.css")}}"/>
	@endpush
@endauth
@guest
	@extends('layout-owee.layout')
@endguest

@section('breadcrumbs')
	@parent
<a href="/forum" title="Forum"><span class="fa fa-wechat module-icon"></span></a>
@endsection


