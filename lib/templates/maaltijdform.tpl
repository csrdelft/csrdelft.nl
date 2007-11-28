<h1>Maaltijd {$maal.formulier.actie}</h1>

<form action="/maaltijden/beheer/" method="post">
	<input type="hidden" name="maalid" value="{$maal.formulier.id}" />
	{if isset($error)}<div class="waarschuwing">{$error}</div>{/if}
	<table>
		<tr>
			<td>Beginmoment</td>
			<td><input type="text" name="moment" value="{$maal.formulier.datum|date_format:$datumFormaatInvoer}" /></td>
		</tr>
		<tr>
			<td>Omschrijving</td>
			<td><input type="text" name="omschrijving" value="{$maal.formulier.tekst}" /></td>
		</tr>
		<tr>
			<td>Limiet</td>
			<td><input type="text" name="limiet" value="{$maal.formulier.max}" /></td>
		</tr>
		<tr>
			<td>Abonnement</td>
			<td>
				{html_options name=abo options=$maal.formulier.abos selected=$maal.formulier.abosoort}
			</td>
		</tr>
		<tr>
			<td>Tafelpraeses</td>
			<td>
				<input type="text" name="tp" value="{$maal.formulier.tp}" style="width: 50px;" />
				{if $maal.formulier.tp!=''}{$maal.formulier.tp|csrnaam} {/if}
			</td>
		</tr>
		<tr>
			<td>Koks</td>
			<td><input type="text" name="koks" value="{$maal.formulier.koks}" style="width: 50px;"  /></td>
		</tr>
		<tr>
			<td>Afwassers</td>
			<td><input type="text" name="afwassers" value="{$maal.formulier.afwassers}" style="width: 50px;" /></td>
		</tr>
		<tr>
			<td>Theedoeken</td>
			<td><input type="text" name="theedoeken" value="{$maal.formulier.theedoeken}" style="width: 50px;" /></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<Td><input type="submit" name="submit" value="opslaan" /></td>
		</tr>
	</table>
</form>
