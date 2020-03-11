<?php
/**
 * @var \CsrDelft\entity\forum\ForumDraad[] $resultaten
 */
?>
@extends('forum.base')

@section('titel', 'Wacht op goedkeuring')

@section('breadcrumbs')
	{!! csr_breadcrumbs([
  '/' => 'main',
  '/forum' => 'Forum',
  '' => 'Wacht',
	]) !!}
@endsection

@section('content')
	{!! getMelding() !!}

	<h1>Wacht op goedkeuring</h1>

	@if($resultaten)
		<div class="forum-zoeken">
			@foreach($resultaten as $draad)
				<div class="forum-zoeken-header">
					<div>
							<span title="Nieuw onderwerp in {{$draad->deel->titel}}">{{$draad->titel}}
								<span>
									[<a href="/forum/deel/{{$draad->forum_id}}">{{$draad->deel->titel}}</a>]
								</span>
								@icon('new')
							</span>
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
						@include('forum.partial.post_lijst', ['draad' => $draad, 'post' => $post])
						<div class="tussenschot"></div>
					@endforeach
				</div>
			@endforeach
		</div>
		<h1>Wacht op goedkeuring</h1>
	@else
		Geen berichten die op goedkeuring wachten.
	@endif
@endsection

