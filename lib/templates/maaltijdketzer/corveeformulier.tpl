<h2 id="corveemaaltijdFormulier">{if $maal.formulier.type == "normaal"}Maaltijdcorvee{else}Huishoudelijke taak{/if} bewerken</h2>

<form action="/actueel/maaltijden/corveebeheer/" method="post">
	<input type="hidden" name="actie" value="{$maal.formulier.actie}" />
	<input type="hidden" name="type" value="{$maal.formulier.type}" />
	<input type="hidden" name="maalid" value="{$maal.formulier.id}" />	
	
	{if isset($error)}<div class="waarschuwing">{$error}</div>{/if}
	<table>
		{if $maal.formulier.actie == "toevoegen"}
			<tr>
				<td style="width: 120px">Beginmoment</td>
				<td colspan="4"><input type="text" name="datum" value="{$maal.formulier.datum|date_format:$datumFormaatInvoer}" /></td>
			</tr>
			<tr>
				<td>Omschrijving</td>
				<td colspan="4"><input type="text" name="tekst" value="{$maal.formulier.tekst}" /></td>
			</tr>
		{else}
			<tr>
				<td style="width: 120px">Beginmoment</td>
				<td colspan="4">{$maal.formulier.datum|date_format:$datumFormaatInvoer}</td>
			</tr>
			<tr>
				<td>Omschrijving</td>
				<td colspan="4">{$maal.formulier.tekst}</td>
			</tr>
		{/if}
			<tr>
				<td />
				<td />
			</tr>
		{if $maal.formulier.type == "normaal"}
			<tr>
				<td>Limiet</td>
				<td colspan="4">
					{$maal.formulier.max}
				</td>
			</tr>
			<tr>
				<td>Abonnement</td>
				<td colspan="4">
					{$maal.formulier.abos[$maal.formulier.abosoort]}
				</td>
			</tr>
			<tr>
				<td>Tafelpraeses</td>
				<td colspan="4">
					{if $maal.formulier.tp!=''}{$maal.formulier.tp|csrnaam}{/if}
				</td>
			</tr>
			<tr>
				<td>Koks</td>
				<td><input type="text" name="koks" value="{$maal.formulier.koks}" style="width: 50px;"  /> ({$maal.formulier.koks_aangemeld} aangemeld)</td>
				<td>&nbsp;</td>
				<td>Punten Kok</td>
				<td><input type="text" name="punten_kok" value="{$maal.formulier.punten_kok}" style="width: 50px;"  /></td>
			</tr>
			<tr>
				<td>Afwassers</td>
				<td><input type="text" name="afwassers" value="{$maal.formulier.afwassers}" style="width: 50px;" /> ({$maal.formulier.afwassers_aangemeld} aangemeld)</td>
				<td>&nbsp;</td>
				<td>Punten Afwas</td>
				<td><input type="text" name="punten_afwas" value="{$maal.formulier.punten_afwas}" style="width: 50px;" /></td>
			</tr>
			<tr>
				<td>Theedoeken</td>
				<td><input type="text" name="theedoeken" value="{$maal.formulier.theedoeken}" style="width: 50px;" /> ({$maal.formulier.theedoeken_aangemeld} aangemeld)</td>
				<td>&nbsp;</td>
				<td>Punten Theedoeken</td>
				<td><input type="text" name="punten_theedoek" value="{$maal.formulier.punten_theedoek}" style="width: 50px;" /></td>
			</tr>
		{else}
			<tr>
				<td>Frituurschoonmakers</td>
				<td><input type="text" name="frituur" value="{$maal.formulier.schoonmaken_frituur}" style="width: 50px;"  /> ({$maal.formulier.frituur_aangemeld} aangemeld)</td>
				<td>&nbsp;</td>
				<td>Punten Frituur</td>
				<td><input type="text" name="punten_schoonmaken_frituur" value="{$maal.formulier.punten_schoonmaken_frituur}" style="width: 50px;"  /></td>
			</tr>
			<tr>
				<td>Afzuigkapschoonmakers</td>
				<td><input type="text" name="afzuigkap" value="{$maal.formulier.schoonmaken_afzuigkap}" style="width: 50px;" /> ({$maal.formulier.afzuigkap_aangemeld} aangemeld)</td>
				<td>&nbsp;</td>
				<td>Punten Afzuigkap</td>
				<td><input type="text" name="punten_schoonmaken_afzuigkap" value="{$maal.formulier.punten_schoonmaken_afzuigkap}" style="width: 50px;" /></td>
			</tr>
			<tr>
				<td>Keukenschoonmakers</td>
				<td><input type="text" name="keuken" value="{$maal.formulier.schoonmaken_keuken}" style="width: 50px;" /> ({$maal.formulier.keuken_aangemeld} aangemeld)</td>
				<td>&nbsp;</td>
				<td>Punten Keuken</td>
				<td><input type="text" name="punten_schoonmaken_keuken" value="{$maal.formulier.punten_schoonmaken_keuken}" style="width: 50px;" /></td>
			</tr>
			<tr>
				<td>Klussers (Licht)</td>
				<td><input type="text" name="lichteklus" value="{$maal.formulier.klussen_licht}" style="width: 50px;" /> ({$maal.formulier.lichteklus_aangemeld} aangemeld)</td>
				<td>&nbsp;</td>
				<td>Punten Lichte Klus</td>
				<td><input type="text" name="punten_klussen_licht" value="{$maal.formulier.punten_klussen_licht}" style="width: 50px;" /></td>
			</tr>
			<tr>
				<td>Klussers (Zwaar)</td>
				<td><input type="text" name="zwareklus" value="{$maal.formulier.klussen_zwaar}" style="width: 50px;" /> ({$maal.formulier.zwareklus_aangemeld} aangemeld)</td>
				<td>&nbsp;</td>
				<td>Punten Zware Klus</td>
				<td><input type="text" name="punten_klussen_zwaar" value="{$maal.formulier.punten_klussen_zwaar}" style="width: 50px;" /></td>
			</tr>
		{/if}
		<tr>
			<td>&nbsp;</td>
			<td colspan="4"><input type="submit" name="submit" value="Opslaan" /></td>
		</tr>
	</table>
</form>
