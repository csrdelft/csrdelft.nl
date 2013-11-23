<thead>
	<tr>
		<th style="width: 80px;">Wijzig</th>
		<th style="width: 70px;">Wanneer</th>
		<th>Titel</th>
		<th>Lijst</th>
		<th>Eters (Limiet)</th>
		<th style="width: 60px;">Status</th>
		<th title="{if $prullenbak}Definitief verwijderen{else}Naar de prullenbak verplaatsen{/if}" style="text-align: center;">
			{if $prullenbak}
				{icon get="cross"}
			{else}
				{icon get="bin_empty"}
			{/if}
		</th>
	</tr>
</thead>