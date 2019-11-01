@extends('maaltijden.base')

@section('titel', 'Boekjaar sluiten')

@section('content')
	@parent

	<p>De maaltijden van het boekjaar zullen naar het archief worden verplaatst.</p>
	<a href="/maaltijden/boekjaar/sluitboekjaar" title="Boekjaar afsluiten" class="btn post popup">@icon("door_in")
		Sluit boekjaar</a>

@endsection
