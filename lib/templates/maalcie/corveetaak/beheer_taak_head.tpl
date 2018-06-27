{*
	beheer_taak_head.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<tr{if isset($datum)} id="taak-datum-head-{$datum}" class="taak-datum-head taak-datum-{$datum}{if !isset($show)} verborgen" onclick="window.maalcie.takenToggleDatum('{$datum}');{/if}"{/if}>
	<th style="width: 100px;">Wijzig</th>
	<th>Gemaild</th>
	<th style="width: 70px;">Datum</th>
	<th>Functie</th>
	<th>Lid</th>
	<th>Punten<br />toegekend</th>
	<th class="text-center">{if $prullenbak}{icon get="cross" title="Definitief verwijderen"}{else}{icon get="bin_empty" title="Naar de prullenbak verplaatsen"}{/if}</th>
</tr>