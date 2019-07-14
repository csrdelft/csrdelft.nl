@extends('layout')

@section('breadcrumbs')
	{!! csr_breadcrumbs([
		'/' => 'main',
		'' => 'Documenten',
	]) !!}
@endsection
