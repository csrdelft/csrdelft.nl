@auth
	@extends('layout')
@endauth
@guest
	@extends('layout-owee.layout')

	@section('styles')
		@stylesheet('extern.css')
		@stylesheet('extern-forum.css')
	@endsection
@endguest

@section('breadcrumbs')
	@parent
<a href="/forum" title="Forum"><span class="fa fa-wechat module-icon"></span></a>
@endsection


