<?php
/**
 * @var \CsrDelft\entity\forum\ForumPost $post
 */
?>
<div id="forumpost-row-{{$post->post_id}}" class="forum-post">
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

		@if(isset($statistiek) && $statistiek)
			<span class="lichtgrijs small"
						title="Gelezen door {{$post->getAantalGelezen()}} van de {{$draad->getAantalLezers()}} lezers">{{sprintf("%.0f", $post->getGelezenPercentage())}}
				% gelezen</span>
		@endif
		<div class="forumpostKnoppen">
			@if($post->wacht_goedkeuring)
				<a href="/forum/goedkeuren/{{$post->post_id}}" class="btn post confirm"
					 title="Bericht goedkeuren">@icon('check')</a>
				<a href="/ttools/stats.php?ip={{$post->auteur_ip}}" class="btn" title="IP-log">@icon('server_chart')</a>
				<a href="/forum/verwijderen/{{$post->post_id}}" class="btn post confirm"
					 title="Verwijder bericht of draad">@icon('cross')</a>
				@if($post->magBewerken())
					<a href="#{{$post->post_id}}"
						 class="@if($post->uid !== CsrDelft\model\security\LoginModel::getUid() && !$post->wacht_goedkeuring)forummodknop @endif"
						 onclick="window.forum.forumBewerken({{$post->post_id}});" title="Bewerk bericht">@icon('pencil')</a>
				@endif
			@else
				@if($post->verwijderd)
					<div class="post-verwijderd">Deze reactie is verwijderd.</div>
					title="Bericht herstellen">@icon('arrow_undo')</a>
				@endif
				@if($post->magCiteren())
					<a href="#reageren" class="btn citeren" data-citeren="{{$post->post_id}}"
						 title="Citeer bericht">@icon('comments')</a>
				@endif
				@if($post->magBewerken())
					<a href="#{{$post->post_id}}"
						 class="@if($post->uid !== CsrDelft\model\security\LoginModel::getUid() && !$post->wacht_goedkeuring)forummodknop @endif"
						 onclick="window.forum.forumBewerken({{$post->post_id}});" title="Bewerk bericht">@icon('pencil')</a>
				@endif
				@auth
					@php($timestamp = $post->datum_tijd->getTimestamp())
					<a id="timestamp{{$timestamp}}" href="/forum/bladwijzer/{{$post->draad_id}}"
						 class="btn post forummodknop bladwijzer" data="timestamp={{$timestamp}}"
						 title="Bladwijzer bij dit bericht leggen">@icon('tab')</a>
				@endauth
				@if($post->getForumDraad()->magModereren())
					<a href="/forum/offtopic/{{$post->post_id}}"
						 class="btn post confirm @if(!$post->wacht_goedkeuring) forummodknop @endif"
						 title="Offtopic markeren">@icon('thumb_down')</a>
					@if(!$post->verwijderd)
						<a href="/forum/verwijderen/{{$post->post_id}}"
							 class="btn post confirm @if(!$post->wacht_goedkeuring) forummodknop @endif"
							 title="Verwijder bericht">@icon('cross')</a>
					@endif
					<a href="/forum/verplaatsen/{{$post->post_id}}"
						 class="btn post prompt @if(!$post->wacht_goedkeuring) forummodknop @endif" title="Verplaats bericht"
						 data="Draad id={{$post->draad_id}}">@icon('arrow_right')</a>
				@endif
			@endif
		</div>

		@auth
			@if($post->uid !== \CsrDelft\model\security\LoginModel::UID_EXTERN)
				<div class="forumpasfoto">{!! \CsrDelft\repository\ProfielRepository::getLink($post->uid, 'pasfoto') !!}</div>
			@endif
		@endauth
	</div>
	<div class="forum-bericht @cycle('bericht0', 'bericht1')" id="post{{$post->post_id}}">
		@php($account = \CsrDelft\model\security\AccountModel::get($post->uid))
		@if($account && \CsrDelft\model\security\AccessModel::mag($account, P_ADMIN))
			{!! bbcode($post->tekst, 'html') !!}
		@else
			{!! bbcode($post->tekst) !!}
		@endif
		@if($post->bewerkt_tekst)
			<div class="bewerkt clear">
				<hr/>
				{!! bbcode($post->bewerkt_tekst) !!}
			</div>
		@endif
	</div>
</div>
