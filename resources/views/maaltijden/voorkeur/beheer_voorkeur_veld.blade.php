<?php
/**
 * @var \CsrDelft\entity\corvee\CorveeVoorkeur $voorkeur
 */
?>
<td id="voorkeur-cell-{{$voorkeur->van_uid}}-{{$crv_repetitie_id}}"
		class="@if(isset($uid)) voorkeur-ingeschakeld @else voorkeur-uitgeschakeld @endif ">
	<a
		href="/corvee/voorkeuren/beheer/{{!empty($uid) ? 'uitschakelen' : 'inschakelen'}}/{{$crv_repetitie_id}}/{{$voorkeur->van_uid}}"
		class="btn post @if(isset($uid)) voorkeur-ingeschakeld @else voorkeur-uitgeschakeld @endif ">
		<input type="checkbox"
					 id="box-{{$voorkeur->van_uid}}-{{$crv_repetitie_id}}"
					 name="vrk-{{$crv_repetitie_id}}"
					 @if(isset($uid)) checked="checked" @endif />
	</a>
</td>
