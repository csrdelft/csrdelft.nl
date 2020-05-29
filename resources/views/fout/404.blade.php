@extends('base')

@section('titel', 'Pagina niet gevonden')

@section('breadcrumbs', '')

@section('content')
	<h1>404: Pagina niet gevonden</h1>
	@if(\CsrDelft\service\security\LoginService::mag(P_ADMIN) && isset($bericht))
		<p>{{$bericht}}</p>
	@endif
	<p>
		De pagina werd niet gevonden, neem contact op met de PubCie als deze pagina wel gevonden zou moeten worden.
	</p>
@endsection
