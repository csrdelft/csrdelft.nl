<div class="zijbalk_forum">
	<div class="zijbalk-kopje">
		@if($belangrijk)
			<a href="/forum/recent/1/belangrijk">Forum belangrijk</a>
		@else
			<a href="/forum/recent">Forum</a>
		@endif
	</div>


	@can('P_FORUM_MOD')
		@if ($aantalWacht > 0)
			&nbsp;<a href="/forum/wacht" class="badge" title="{{$aantalWacht}} forumbericht{{($aantalWacht === 1 ? '' : 'en')}} wacht{{($aantalWacht === 1 ? '' : 'en')}} op goedkeuring">{{$aantalWacht}}</a>
		@endif
	@endcan
	@foreach($draden as $draad)
		@php($timestamp = strtotime($draad->laatst_gewijzigd))

		@if(CsrDelft\model\LidInstellingenModel::get('forum', 'open_draad_op_pagina') == 'ongelezen')
			@php($urlHash = '#ongelezen')
		@elseif(CsrDelft\model\LidInstellingenModel::get('forum', 'open_draad_op_pagina') == 'laatste')
			@php($urlHash = '#reageren')
		@else
			@php($urlHash = '')
		@endif

		@php($ongelezenWeergave = CsrDelft\model\LidInstellingenModel::get('forum', 'ongelezenWeergave'))

		<div class="item" id="forumdraad-row-{{$draad->draad_id}}">
			<a href="/forum/onderwerp/{{$draad->draad_id}}{{$urlHash}}" title="{{$draad->titel}}"
				 @auth @if($draad->isOngelezen()) class="{{$ongelezenWeergave}}" @endif @endauth>
				<span class="zijbalk-moment">{{zijbalk_date_format($timestamp)}}</span>&nbsp;{{$draad->titel}}
			</a>
		</div>
	@endforeach
</div>
