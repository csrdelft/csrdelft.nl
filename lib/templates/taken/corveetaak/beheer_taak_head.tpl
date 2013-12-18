{*
	beheer_taak_head.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<tr{if isset($datum)} id="taak-datum-head-{$datum}" class="taak-datum-{$datum}"{if !isset($show)} style="display: none;" onclick="taken_toggle_datum('{$datum}');"{/if}{/if}>
	<th style="width: 50px;">Wijzig</th>
	<th>Gemaild</th>
	<th style="width: 60px;">Datum</th>
	<th>Functie</th>
	<th>Lid</th>
	<th>Punten<br />toegekend</th>
	<th style="text-align: center;">{strip}
		<a class="knop{if $prullenbak} confirm{/if}" onclick="event.stopPropagation();taken_delete_range(this);" title="Selectie {if $prullenbak}definitief verwijderen{else}naar de prullenbak verplaatsen{/if}">
		{if $prullenbak}
			{icon get="cross"}
		{else}
			{icon get="bin_empty"}
		{/if}
		</a>
	</th>{/strip}
</tr>