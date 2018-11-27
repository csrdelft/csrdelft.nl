@extends('layout')

@push('styles')
	<link rel="stylesheet" href="{{asset("/dist/css/module-documenten.css")}}"/>
@endpush

@section('breadcrumbs')
	<a href="/documenten" title="Documenten"><span class="fa fa-file-text module-icon"></span></a>
@endsection
