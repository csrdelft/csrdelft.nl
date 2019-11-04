<td id="voorkeur-cell-{{$voorkeur->getVanUid()}}-{{$crid}}"
		class="@if(isset($uid)) voorkeur-ingeschakeld @else voorkeur-uitgeschakeld @endif ">
	<a
		href="/corvee/voorkeuren/beheer/{{!empty($uid) ? 'uitschakelen' : 'inschakelen'}}/{{$crid}}/{{$voorkeur->getVanUid()}}"
		class="btn post @if(isset($uid)) voorkeur-ingeschakeld @else voorkeur-uitgeschakeld @endif ">
		<input type="checkbox"
					 id="box-{{$voorkeur->getVanUid()}}-{{$crid}}"
					 name="vrk-{{$crid}}"
					 @if(isset($uid)) checked="checked" @endif />
	</a>
</td>
