@auth
	@extends('layout')
@endauth
@guest
	@extends('layout-owee.layout')

@section('styles')
	@stylesheet('extern.css')
@endsection
@endguest
