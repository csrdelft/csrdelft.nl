{*
	beheer_taak_head.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<thead>
	<tr>
		<th>Wijzig</th>
		<th>Herinnering<br />verstuurd</th>
		<th>Wanneer</th>
		<th>Functie</th>
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