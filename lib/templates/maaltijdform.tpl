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
				{if $maal.formulier.tp!=''}{$maal.formulier.tp} {/if}
				<input type="text" name="tp" value="{$maal.formulier.tp_uid}" style="width: 50px;" /></td>
		</tr>
	<!--	<tr>
			<td>Koks</td>
			<td>
				{if $maal.formulier.kok1!=''}{$maal.formulier.kok1} {/if}
				<input type="text" name="kok1" value="{$maal.formulier.kok1_uid}" style="width: 50px;" /><br />
				{if $maal.formulier.kok2!=''}{$maal.formulier.kok2} {/if}
				<input type="text" name="kok2" value="{$maal.formulier.kok2_uid}" style="width: 50px;" /></td>
		</tr>
		<tr>
			<td>Afwassers</td>
			<td>
				{if $maal.formulier.afw1!=''}{$maal.formulier.afw1} {/if}
				<input type="text" name="afw1" value="{$maal.formulier.afw1_uid}" style="width: 50px;" /><br />
				{if $maal.formulier.afw2!=''}{$maal.formulier.afw2} {/if}
				<input type="text" name="afw2" value="{$maal.formulier.afw2_uid}" style="width: 50px;" /><br />
				{if $maal.formulier.afw3!=''}{$maal.formulier.afw3} {/if}
				<input type="text" name="afw3" value="{$maal.formulier.afw3_uid}" style="width: 50px;" /></td>
		</tr>
		-->
		<tr>
			<td>&nbsp;</td>
			<Td><input type="submit" name="submit" value="opslaan" /></td>
		</tr>
	</table>
</form>
