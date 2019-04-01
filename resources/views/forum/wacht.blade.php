@extends('forum.base')

@section('titel', 'Wacht op goedkeuring')

@section('content')
	{!! getMelding() !!}

	<h1>Wacht op goedkeuring</h1>

	@if($resultaten)
		<div class="forum-zoeken">
			@foreach($resultaten as $draad)
				<div class="forum-zoeken-header">
					<div>
							<span title="Nieuw onderwerp in {{$draad->getForumDeel()->titel}}">{{$draad->titel}}
								<span>
									[<a href="/forum/deel/{{$draad->forum_id}}">{{$draad->getForumDeel()->titel}}</a>]
								</span>
								@icon('new')
							</span>
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
						<div id="forumpost-row-{{$post->post_id}}" class="forum-post @if($post->gefilterd) verborgen @endif">
							<div class="auteur">
								<div class="postpijl">
									<a class="postanchor" id="{{$post->post_id}}"></a>
									<a class="postlink" href="/forum/reactie/{{$post->post_id}}#{{$post->post_id}}"
										 title="Link naar deze post">&rarr;</a>
								</div>
								<div class="naam">
									{!! CsrDelft\model\ProfielModel::getLink($post->uid, 'user') !!}
								</div>
								<span class="moment">
									@if(\CsrDelft\model\LidInstellingenModel::get('forum', 'datumWeergave') === 'relatief')
										{!! reldate($post->datum_tijd) !!}
									@else
										{{$post->datum_tijd}}
									@endif
								</span>
							</div>
							<div class="forum-bericht @cycle('bericht0', 'bericht1')" id="post{{$post->post_id}}">
								{!! bbcode($post->tekst) !!}
							</div>
						</div>
						<div class="tussenschot"></div>
					@endforeach
				</div>
			@endforeach
		</div>
		<h1>Wacht op goedkeuring</h1>
		@yield('breadcrumbs')
	@else
		Geen berichten die op goedkeuring wachten.
	@endif
@endsection

