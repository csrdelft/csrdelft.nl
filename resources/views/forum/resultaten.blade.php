<?php
/**
 * @var \CsrDelft\entity\forum\ForumDraad[] $resultaten
 * @var \CsrDelft\view\forum\ForumZoekenForm $form
 */
?>
@extends('forum.base')

@section('titel')
	@if(isset($query))
		Resultaten voor {{$query}}
	@else
		Wacht op goedkeuring
	@endif
@endsection

@section('breadcrumbs')
	{!! csr_breadcrumbs([
  '/' => 'main',
  '/forum' => 'Forum',
  '' => 'Zoeken',
	]) !!}
@endsection

@section('content')
	{!! getMelding() !!}

	<h1>{{$titel}}</h1>

	<div class="forum-zoeken">
		@php($form->view())
	</div>

	@if($resultaten)
		<div class="forum-zoeken">
			@foreach($resultaten as $draad)
				<div class="forum-zoeken-header">
					<div>
						<a id="{{$draad->draad_id}}"
							 href="/forum/onderwerp/{{$draad->draad_id}}"
							 @if($draad->isOngelezen())class="{{lid_instelling('forum', 'ongelezenWeergave')}}" @endif>
							{!! highlight_zoekterm($draad->titel, $query) !!}
						</a>
						@if($draad->belangrijk)
							@icon($draad->belangrijk, null, 'Dit onderwerp is door het bestuur aangemerkt als belangrijk')
						@elseif($draad->gesloten)
							@icon('lock', null, 'Dit onderwerp is gesloten, u kunt niet meer reageren')
						@endif
						<span>[<a href="/forum/deel/{{$draad->forum_id}}">{{$draad->getForumDeel()->titel}}</a>]</span>
					</div>
					<div class="niet-dik">
						@if(lid_instelling('forum', 'datumWeergave') === 'relatief')
							{!! reldate($draad->datum_tijd) !!}
						@else
							{{$draad->datum_tijd}}
						@endif
					</div>
				</div>
				<div class="forum-zoeken-bericht">
					@foreach($draad->getForumPosts() as $post)
						<div id="forumpost-row-{{$post->post_id}}" class="forum-post @if($post->gefilterd) verborgen @endif">
							<div class="auteur">
								<div class="postpijl">
									<a class="postanchor" id="{{$post->post_id}}"></a>
									<a class="postlink" href="/forum/reactie/{{$post->post_id}}#{{$post->post_id}}"
										 title="Link naar deze post">&rarr;</a>
								</div>
								<div class="naam">
									{!! \CsrDelft\repository\ProfielRepository::getLink($post->uid, 'user') !!}
								</div>
								<span class="moment">
									@if(lid_instelling('forum', 'datumWeergave') === 'relatief')
										{!! reldate($post->datum_tijd) !!}
									@else
										{{$post->datum_tijd->format(DATETIME_FORMAT)}}
									@endif
								</span>
								@auth
									@if($post->uid !== \CsrDelft\model\security\LoginModel::UID_EXTERN)
										<div class="forumpasfoto">{!! \CsrDelft\repository\ProfielRepository::getLink($post->uid, 'pasfoto') !!}</div>
									@endif
								@endauth
							</div>
							<div class="forum-bericht @cycle('bericht0', 'bericht1')" id="post{{$post->post_id}}">
								{!! highlight_zoekterm(bbcode_light(split_on_keyword($post->tekst, $query)), $query) !!}
							</div>
						</div>
						<div class="tussenschot"></div>
					@endforeach
				</div>
			@endforeach
		</div>
		<h1>{{$titel}}</h1>
		@yield('breadcrumbs')
	@else
		Geen resultaten.
	@endif
@endsection
