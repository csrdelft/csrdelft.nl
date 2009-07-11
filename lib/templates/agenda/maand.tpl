<h1>Agenda {$maand} {$jaar}</h1>
<table class="agenda maand">
	<tr class="knoppen">
		<td><a class="knop">&laquo; Vorige maand</a></td>
		<td colspan="5"> </td>
		<td><a class="knop">Volgende maand &raquo;</a></td>
	</tr>
	{foreach from=$dagen item=dag}
		<tr>		
			{foreach from=$dagen item=dag}
				<td>
					Blaa
				</td>
			{/foreach}
		</tr>
	{/foreach}
</table>