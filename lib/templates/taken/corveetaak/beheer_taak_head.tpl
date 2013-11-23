{*
	beheer_taak_head.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<thead>
	<tr>
		<th style="width: 80px;">Wijzig</th>
		<th>Gemaild</th>
		<th style="width: 70px;">Wanneer</th>
		<th>Functie</th>
		<th style="width: 130px;">Lid</th>
		<th>Punten<br />toegekend</th>
		<th title="{if $prullenbak}Definitief verwijderen{else}Naar de prullenbak verplaatsen{/if}" style="text-align: center;">{strip}
			{if $prullenbak}
				{icon get="cross"}
			{else}
				{icon get="bin_empty"}
			{/if}
		</th>{/strip}
	</tr>
</thead>