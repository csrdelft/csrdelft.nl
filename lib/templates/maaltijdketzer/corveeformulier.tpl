<h2 id="corveemaaltijdFormulier">Maaltijd Corvee {$maal.formulier.actie}</h2>

<form action="/actueel/maaltijden/corveebeheer/" method="post">
	<input type="hidden" name="maalid" value="{$maal.formulier.id}" />
	<input type="hidden" name="actie" value="gewoonbewerk" />
	{if isset($error)}<div class="waarschuwing">{$error}</div>{/if}
	<table>
		<tr>
			<td style="width: 120px">Beginmoment</td>
			<td>{$maal.formulier.datum|date_format:$datumFormaatInvoer}</td>
		</tr>
		<tr>
			<td>Omschrijving</td>
			<td>{$maal.formulier.tekst}</td>
		</tr>
		<tr>
			<td>Limiet</td>
			<td>{$maal.formulier.max}</td>
		</tr>
		<tr>
			<td>Abonnement</td>
			<td>
				{$maal.formulier.abos[$maal.formulier.abosoort]}
			</td>
		</tr>
		<tr>
			<td>Tafelpraeses</td>
			<td>
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
			<td>Punten Kok</td>
			<td><input type="text" name="punten_kok" value="{$maal.formulier.punten_kok}" style="width: 50px;"  /></td>
		</tr>
		<tr>
			<td>Punten Afwas</td>
			<td><input type="text" name="punten_afwas" value="{$maal.formulier.punten_afwas}" style="width: 50px;" /></td>
		</tr>
		<tr>
			<td>Punten Theedoeken</td>
			<td><input type="text" name="punten_theedoek" value="{$maal.formulier.punten_theedoek}" style="width: 50px;" /></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<Td><input type="submit" name="submit" value="opslaan" /></td>
		</tr>
	</table>
</form>
