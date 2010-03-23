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
	
    <div>
        <div style="float:left">
            <br/>
            <label for="koks">Koks</label><br/>
            <label for="afwassers">Afwassers</label><br />
            <label for="theedoeken">Theedoeken</label><br />
        </div>
        <div style="float:left">
            Aantal:<br />
            <input type="text" name="koks" value="{$maal.formulier.koks}" style="width: 50px;"  /><br />            
            <input type="text" name="afwassers" value="{$maal.formulier.afwassers}" style="width: 50px;" /><br />            
            <input type="text" name="theedoeken" value="{$maal.formulier.theedoeken}" style="width: 50px;" /><br />
        </div>
        {if $maal.formulier.id == 0}
        <div style="float:left; position:relative; left:10px">
            Corveepunten:<br />
            <input type="text" name="punten_kok" value="{$maal.formulier.punten_kok}" style="width: 50px;"  /><br />            
            <input type="text" name="punten_afwas" value="{$maal.formulier.punten_afwas}" style="width: 50px;" /><br />            
            <input type="text" name="punten_theedoek" value="{$maal.formulier.punten_theedoek}" style="width: 50px;" /><br />        
        </div>
        {/if}
        <div style="clear:both" />
    </div>
	<label for="submit">&nbsp;</label>
	<input type="submit" name="submit" value="Opslaan" /><br />
    
</form>
