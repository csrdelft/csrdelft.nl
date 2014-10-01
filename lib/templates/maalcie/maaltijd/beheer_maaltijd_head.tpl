<thead>
	<tr>
	{if $archief}
		<th style="width: 120px;">Wanneer</th>
		<th style="width: 200px;">Titel</th>
		<th style="width: 60px;">Id</th>
		<th style="width: 60px;">Prijs</th>
		<th>Aanmeldingen</th>
	{else}
		<th style="width: 80px;">Wijzig</th>
		<th style="width: 70px;">Datum</th>
		<th>Titel</th>
		<th style="width: 60px;">Lijst</th>
		<th style="text-align: right;">Eters</th>
		<th style="width: 80px;">(Limiet)</th>
		<th>Status</th>
		<th class="center-text">{if $prullenbak}{icon get="cross" title="Definitief verwijderen"}{else}{icon get="bin_empty" title="Naar de prullenbak verplaatsen"}{/if}</th>
	{/if}
	</tr>
</thead>