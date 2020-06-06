<?php
/**
 * @var \CsrDelft\entity\forum\ForumPost[] $posts
 */
?>
<div class="zijbalk_forum">
	<div class="zijbalk-kopje">
		<a href="/profiel/{{\CsrDelft\service\security\LoginService::getUid()}}#forum">Forum (zelf gepost)</a>
	</div>
	@foreach($posts as $post)
		@php($timestamp = $post->datum_tijd->getTimestamp())
		@php($draad = $post->draad)
		@php($ongelezenWeergave = lid_instelling('forum', 'ongelezenWeergave'))
		<div class="item">
			<a href="/forum/reactie/{{$post->post_id}}#{{$post->post_id}}" title="{{$draad->titel}}"
				 @if($draad->isOngelezen())class="{{$ongelezenWeergave}}" @endif>
				<span class="zijbalk-moment">{{zijbalk_date_format($timestamp)}}</span>&nbsp;{{$draad->titel}}
			</a>
		</div>
	@endforeach
</div>
