{strip}
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
		<th>Eters (Limiet)</th>
		<th style="width: 80px;"> </th>
		<th>Status</th>
		<th style="text-align: center;">
			<a name="del-maaltijd" class="knop{if $prullenbak} confirm{/if} range" title="Selectie {if $prullenbak}definitief verwijderen{else}naar de prullenbak verplaatsen{/if}">
			{if $prullenbak}
				{icon get="cross"}
			{else}
				{icon get="bin_empty"}
			{/if}
			</a>
		</th>
	{/if}
	</tr>
</thead>
{/strip}