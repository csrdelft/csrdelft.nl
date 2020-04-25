<?php
/**
 * @var \CsrDelft\entity\corvee\CorveeVoorkeur $voorkeur
 */
?>
<td id="voorkeur-cell-{{$voorkeur->van_uid}}-{{$crid}}"
		class="@if(isset($uid)) voorkeur-ingeschakeld @else voorkeur-uitgeschakeld @endif ">
	<a
		href="/corvee/voorkeuren/beheer/{{!empty($uid) ? 'uitschakelen' : 'inschakelen'}}/{{$crid}}/{{$voorkeur->van_uid}}"
		class="btn post @if(isset($uid)) voorkeur-ingeschakeld @else voorkeur-uitgeschakeld @endif ">
		<input type="checkbox"
					 id="box-{{$voorkeur->van_uid}}-{{$crid}}"
					 name="vrk-{{$crid}}"
					 @if(isset($uid)) checked="checked" @endif />
	</a>
</td>
