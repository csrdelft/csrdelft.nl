<h2 id="corveepuntenFormulier">Maaltijd Corveepunten bewerken</h2>
 
<form name="puntenbewerk" action="/actueel/maaltijden/corveebeheer/" method="post">
	<input type="hidden" name="actie" value="puntenbewerk" />
	<input type="hidden" name="type" value="{$maal.formulier.type}" />
	<input type="hidden" name="maalid" value="{$maal.formulier.id}" />
	<input type="hidden" id="filter" name="filter" value="{$maal.formulier.filter}" />

	<table>
		{foreach from=$maal.formulier.taken.toegekend key=lid item=selected}
		<tr>
			<td>
				{$lid|string_format:'%04d'|csrnaam}
			</td>
			<td>
				{$selected}
			</td>
			<td>
				{if $selected=="onbekend"}	
					{html_options name=punten[$lid] options=$maal.formulier.pt_opties selected="ja"}
				{else}
					{html_options name=punten[$lid] options=$maal.formulier.pt_opties selected=$selected}
				{/if}
			</td>
		</tr>
		{/foreach}
		<tr>
			<td>&nbsp;</td>
			<td>
				<input type="submit" name="opslaan" value="Opslaan" /> 
			</td>
		</tr>
	</table>
</form>