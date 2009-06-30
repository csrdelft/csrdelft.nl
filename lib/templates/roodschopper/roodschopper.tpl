<h1>Roodschopper</h1>

<form action="roodschopper.php" method="post">
	<fieldset>
	<label for="commissie">Commissie:</label>
	<select name="commissie" id="commissie">
		<option value="maalcie" {if $roodschopper->getCommissie()=='maalcie'}selected="selected"{/if}>MaalCie</option>
		<option value="soccie" {if $roodschopper->getCommissie()=='soccie'}selected="selected"{/if}>SocCie</option>
	</select><br />

	<label for="bcc">BCC naar:</label>
	<input type="text" id="bcc" name="bcc" value="{$roodschopper->getBcc()}" /><br />
	
	<label for="saldogrens">Saldogrens:</label>
	<input type="text" id="saldogrens" name="saldogrens" value="{$roodschopper->getSaldogrens()}" /><br />

	<label for="uitsluiten">Geen mail naar:</label>
	<input type="text" id="uitsluiten" name="uitsluiten" value="{$roodschopper->getUitgesloten()}" /><br /><br />

	<label for="onderwerp">Onderwerp:</label>
	<input type="text" id="onderwerp" name="onderwerp" value="{$roodschopper->getOnderwerp()}" /><br />
	
	<div id="berichtContainer">
		<div id="berichtPreviewContainer" class="previewContainer">
			<div id="berichtPreview" class="preview"></div>
		</div>
	</div>
	<label for="berichtInvoer">Mailbericht:<br /><br />
	<em class="small">Variabelen:<br />
	LID = Naam van lid<br />
	SALDO = Saldo van lid</em></label>
	<textarea name="bericht" id="berichtInvoer" rows="10" cols="80">{$roodschopper->getBericht()}</textarea><br />
	
	
	<label for="submit">&nbsp;</label>
	<a class="handje knop" title="Opmaakhulp weergeven" onclick="toggleDiv('ubbhulpverhaal')" style="float: right;">UBB</a>
	<a class="handje knop" title="Vergroot het invoerveld" onclick="vergrootTextarea('berichtInvoer', 10)" style="float: right;"><strong>↑↓</strong></a>

	<input type="submit" name="submit" id="submit" value="Verzenden" />
	<input id="forumVoorbeeld" type="button" onclick="previewPost('berichtInvoer', 'berichtPreview')" style="color: rgb(119, 119, 119);" value="voorbeeld"/>
	</fieldset>
</form>
<br />
<img src="http://csrdelft.nl/tools/saldografiek.php?uid=000&timespan=100&{$roodschopper->getCommissie()}" />
