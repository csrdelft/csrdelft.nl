@auth
	@extends('layout')
@endauth
@guest
	@extends('layout-extern.layout')

@section('styles')
	@stylesheet('extern')
@endsection
@endguest
