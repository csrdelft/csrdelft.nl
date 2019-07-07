@extends('layout')

@section('breadcrumbs')
	@php(\CsrDelft\model\MenuModel::instance()->renderBreadcrumbs([
		(object) ['link' => '/', 'tekst' => 'main'],
		(object) ['link' => '/documenten', 'tekst' => 'Documenten'],
	]))
@endsection
