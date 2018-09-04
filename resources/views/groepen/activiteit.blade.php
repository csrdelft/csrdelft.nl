@extends('groepen.ketzer')

@unless(empty($groep->locatie))
	@section('location')
		&nbsp; <a target="_blank" href="https://maps.google.nl/maps?q={!! urlencode($groep->locatie) !!}"
						title="{{$groep->locatie}}" class="lichtgrijs fa fa-map-marker fa-lg"></a>
	@endsection
@endunless

