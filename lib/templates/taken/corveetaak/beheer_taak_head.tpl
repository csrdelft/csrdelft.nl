{*
	beheer_taak_head.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<tr{if isset($datum)} id="taak-datum-head-{$datum}" class="taak-datum-{$datum}"{if !isset($show)} style="display: none;" onclick="toggle_taken_datum('{$datum}');"{/if}{/if}>
	<th style="width: 50px;">Wijzig</th>
	<th>Gemaild</th>
	<th style="width: 60px;">Datum</th>
	<th>Functie</th>
	<th>Lid</th>
	<th>Punten<br />toegekend</th>
	<th title="{if $prullenbak}Definitief verwijderen{else}Naar de prullenbak verplaatsen{/if}" style="text-align: center;">{strip}
		{if $prullenbak}
			{icon get="cross"}
		{else}
			{icon get="bin_empty"}
		{/if}
	</th>{/strip}
</tr>