<div class="row align-items-baseline">

<h3 class="col">{{$deel->titel}}</h3> <div class="col-auto"><a href="{{$locatie}}">Meer...</a></div>
</div>
<div class="card-group mb-3">
	@php($ongelezenWeergave = CsrDelft\model\LidInstellingenModel::get('forum', 'ongelezenWeergave'))
	@foreach ($deel->getForumDraden() as $draad)
		@php($laatste_post = \CsrDelft\model\forum\ForumPostsModel::get($draad->laatste_post_id))

		<div class="card">
			<div class="card-body d-flex flex-column" style="max-height: 250px;">
				<h5 class="card-title flex-shrink-0" style="overflow:hidden; text-overflow: ellipsis; white-space: nowrap;">
					<a href="/forum/onderwerp/{{$draad->draad_id}}" title="{{$draad->titel}}"
						 @auth @if($draad->isOngelezen()) class="{{$ongelezenWeergave}}" @endif @endauth>
					{{$draad->titel}}</a>
				</h5>
				<div class="card-text flex-grow-1" style="max-height: 200px; overflow: hidden; text-overflow: ellipsis;">{!! bbcode($laatste_post->tekst) !!}</div>
				<div class="card-text"><small class="text-muted">{!! reldate($draad->laatst_gewijzigd) !!} door {!! \CsrDelft\model\ProfielModel::getLink($laatste_post->uid) !!}</small></div>
			</div>
		</div>

	@endforeach
</div>
