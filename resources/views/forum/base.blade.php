@auth
	@extends('layout')
@endauth
@guest
	@extends('layout-extern.layout')

	@section('styles')
		@stylesheet('extern.css')
		@stylesheet('extern-forum.css')
	@endsection
@endguest
