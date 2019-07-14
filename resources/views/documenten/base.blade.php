@extends('layout')

@section('breadcrumbs')
	@php(\CsrDelft\model\MenuModel::instance()->renderBreadcrumbs([
		'/' => 'main',
		'' => 'Documenten',
	]))
@endsection
