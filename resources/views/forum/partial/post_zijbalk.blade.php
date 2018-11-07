<div class="zijbalk_forum">
	<div class="zijbalk-kopje">
		<a href="/profiel/{{\CsrDelft\model\security\LoginModel::getUid()}}/#forum">Forum (zelf gepost)</a>
	</div>
	@foreach($posts as $post)
		@php($timestamp = strtotime($post->datum_tijd))
		@php($draad = $post->getForumDraad())
		@php($ongelezenWeergave = CsrDelft\model\LidInstellingenModel::get('forum', 'ongelezenWeergave'))
		<div class="item">
			<a href="/forum/reactie/{{$post->post_id}}#{{$post->post_id}}" title="{{$draad->titel}}"
				 @if($draad->isOngelezen())class="{{$ongelezenWeergave}}" @endif>
				<span class="zijbalk-moment">{{zijbalk_date_format($timestamp)}}</span>&nbsp;{{$draad->titel}}
			</a>
		</div>
	@endforeach
</div>
