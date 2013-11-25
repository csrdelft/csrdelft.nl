{*
	beheer_taak_head.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
{strip}
<thead>
	<tr{if isset($datum)} class="taak-datum-{$datum}"{if !isset($maaltijd)} style="display: none;"{/if}{/if}>
		<th style="width: 50px;">Wijzig</th>
		<th>Gemaild</th>
		<th style="width: 60px;">Datum</th>
		<th>
			Functie
			{if isset($mid) and !isset($maaltijd)}
			<div style="float: right;">
				<a href="/maaltijdenbeheer/beheer/{$mid}" title="Beheer maaltijdcorvee" class="knop get">{icon get="cup_link"}</a>
			</div>
			{/if}
		</th>
		<th>Lid</th>
		<th>Punten<br />toegekend</th>
		<th title="{if $prullenbak}Definitief verwijderen{else}Naar de prullenbak verplaatsen{/if}" style="text-align: center;">
			{if $prullenbak}
				{icon get="cross"}
			{else}
				{icon get="bin_empty"}
			{/if}
		</th>
	</tr>
</thead>
{/strip}