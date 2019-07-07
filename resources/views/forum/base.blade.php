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
