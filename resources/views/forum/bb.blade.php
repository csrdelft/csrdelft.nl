@php($ongelezenWeergave = lid_instelling('forum', 'ongelezenWeergave'))
@if(lid_instelling('forum', 'open_draad_op_pagina') == 'ongelezen')
	@php($urlHash = "#ongelezen")
@elseif(lid_instelling('forum', 'open_draad_op_pagina') == 'laatste')
	@php($urlHash = "#reageren")
@else
	@php($urlHash = "")
@endif

<div class="my-3 p-3 bg-white rounded shadow-sm">
<h6 class="border-bottom border-gray pb-2 mb-0">{{$deel->titel}}</h6>
@foreach($deel->getForumDraden() as $draad)
	<div class="media pt-3">
		<a href="/forum/onderwerp/{{$draad->draad_id}}{{$urlHash}}" class="media-body pb-3 mb-0 lh-125 border-bottom border-gray @auth @if($draad->isOngelezen()) {{$ongelezenWeergave}}@endif @endauth ">
			@if($draad->belangrijk)
				@icon($draad->belangrijk, null, 'dit onderwerp is door het bestuur aangemerkt als belangrijk')
			@elseif($draad->plakkerig)
				@icon('note', null, 'Dit onderwerp is plakkerig, het blijft bovenaan')
			@elseif($draad->gesloten)
				@icon('lock', null, 'Dit onderwerp is gesloten, u kunt niet meer reageren')
			@endif
			<strong>{{$draad->titel}}</strong>
			@auth
				@if($draad->getAantalOngelezenPosts() > 0)
					<span class="badge">{{$draad->getAantalOngelezenPosts()}}</span>
				@endif
			@endauth
			<span class="text-muted float-right">{!! reldate($draad->laatst_gewijzigd) !!}</span>
		</a>
	</div>
@endforeach
	<small class="d-block text-right mt-3">
		<a href="/forum/deel/{{$deel->forum_id}}">Meer lezen...</a>
	</small>
</div>
