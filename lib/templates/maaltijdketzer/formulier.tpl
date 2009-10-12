<h2 id="maaltijdFormulier">Maaltijd {$maal.formulier.actie}</h2>

<form action="/actueel/maaltijden/beheer/" method="post" id="maaltijdform">
	<input type="hidden" name="maalid" value="{$maal.formulier.id}" />
	{if isset($error)}<div class="waarschuwing">{$error}</div>{/if}
	<label for="datum">Beginmoment</label>
	<input type="text" name="datum" value="{$maal.formulier.datum|date_format:$datumFormaatInvoer}" /><br />

	<label for="tekst">Omschrijving</label>
	<input type="text" name="tekst" value="{$maal.formulier.tekst}" /><br />

	<label for="limiet">Limiet</label>
	<input type="text" name="limiet" value="{$maal.formulier.max}" /><br />

	<label for="abo">Abonnement</label>
	{html_options name=abo options=$maal.formulier.abos selected=$maal.formulier.abosoort}<br />

	<label for="tp">Tafelpraeses</label>
	<input type="text" name="tp" id="field_tp" value="{$maal.formulier.tp}" style="width: 50px;" onKeyUp="uidPreview('tp')" /><div class="uidPreview" id="preview_tp"></div>
	<script>uidPreview('tp');</script><br />
	
	<label for="koks">Koks</label>
	<input type="text" name="koks" value="{$maal.formulier.koks}" style="width: 50px;"  /><br />

	<label for="afwassers">Afwassers</label>
	<input type="text" name="afwassers" value="{$maal.formulier.afwassers}" style="width: 50px;" /><br />
	
	<label for="theedoeken">Theedoeken</label>
	<input type="text" name="theedoeken" value="{$maal.formulier.theedoeken}" style="width: 50px;" /><br />
	<label for="submit">&nbsp;</label>
	<input type="submit" name="submit" value="opslaan" /><br />
</form>
