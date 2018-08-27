@extends('forum.base')

@section('titel')
	@if(isset($query))
		Resultaten voor {{$query}}
	@else
		Wacht op goedkeuring
	@endif
@endsection

@section('content')
	{!! getMelding() !!}

	<h1>{{$titel}}</h1>

	@if($resultaten)
		<div class="forum-zoeken">
			<table id="forumtabel">
				@foreach($resultaten as $draad)
					<div class="forum-zoeken-header">

						<div>
							@if($draad->wacht_goedkeuring)
								<span title="Nieuw onderwerp in {{$draad->getForumDeel()->titel}}">{{$draad->titel}}<span>[<a
											href="/forum/deel/{{$draad->forum_id}}">{{$draad->getForumDeel()->titel}}</a>]</span>@icon('new')</span>
							@else
								<a id="{{$draad->draad_id}}"
									 href="/forum/onderwerp/{{$draad->draad_id}}"
									 @if($draad->isOngelezen())class="{{CsrDelft\model\LidInstellingenModel::get('forum', 'ongelezenWeergave')}}" @endif>
									{{$draad->titel}}
								</a>
								@if($draad->belangrijk)
									@icon($draad->belangrijk, null, 'Dit onderwerp is door het bestuur aangemerkt als belangrijk')
								@elseif($draad->gesloten)
									@icon('lock', null, 'Dit onderwerp is gesloten, u kunt niet meer reageren')
								@endif
								<span>[<a href="/forum/deel/{{$draad->forum_id}}">{{$draad->getForumDeel()->titel}}</a>]</span>
							@endif
						</div>
						<div class="niet-dik">
							@if(\CsrDelft\model\LidInstellingenModel::get('forum', 'datumWeergave') === 'relatief')
								{!! reldate($draad->datum_tijd) !!}
							@else
								{{$draad->datum_tijd}}
							@endif
						</div>
					</div>
					<div class="forum-zoeken-bericht">
						@foreach($draad->getForumPosts() as $post)
							@include('forum.partial.post_lijst', ['draad' => $draad, 'post' => $post])
							<div class="tussenschot"></div>
						@endforeach
					</div>
				@endforeach
				@if(isset($query))
					<div class="forum-zoeken-footer">
						{!! sliding_pager([
            'baseurl' => "/forum/zoeken/$query/",
            'pagecount' => \CsrDelft\model\forum\ForumDradenModel::instance()->getHuidigePagina(),
            'curpage' => \CsrDelft\model\forum\ForumDradenModel::instance()->getHuidigePagina(),
            'separator' => ' &nbsp;'
            ]) !!}
						{{--&nbsp;<a
							href="/forum/zoeken/{{$query}}/{{\CsrDelft\model\forum\ForumDradenModel::instance()->getAantalPaginas(0)}}">verder
							zoeken</a> TODO: Fixen, is kneiterbrak--}}
					</div>
				@endif
			</table>
		</div>
		<h1>{{$titel}}</h1>
		@yield('breadcrumbs')

	@else
		Geen resultaten.
	@endif
@endsection
