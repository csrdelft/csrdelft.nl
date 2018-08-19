{getMelding()}
<h1>Roodschopper</h1>
<p>Met deze tool kan de SocCie en de MaalCie zelf een roodschopmail versturen. U stelt een aantal parameters in, typt een verhaaltje,
	en drukt op 'Verder gaan'. Dan krijgt u een overzichtje te zien van de mensen die rood staan, en kunt u het verzenden bevestigen.</p>
{if !isSyrinx()}<p><br />Niet op csrdelft.nl server: Debugmode staat aan.</p>{/if}

<form id="roodschopper" action="roodschopper.php" method="post">
	<fieldset>
	<label for="commissie">Commissie:</label>
	<select name="commissie" id="commissie">
		<option value="maalcie" {if $roodschopper->getCommissie()=='maalcie'}selected="selected"{/if}>MaalCie</option>
		<option value="soccie" {if $roodschopper->getCommissie()=='soccie'}selected="selected"{/if}>SocCie</option>
	</select><br />

	<label for="from">Afzenderadres:</label>
	<input type="text" id="from" name="from" value="{$roodschopper->getFrom()}" /> <span class="small">Als afzenderadres gebruiken.</span><br />
	
	<label for="bcc">BCC naar:</label>
	<input type="text" id="bcc" name="bcc" value="{$roodschopper->getBcc()}" /> <span class="small">alle verzonden mails BCCen naar dit adres.</span><br />
	
	<label for="saldogrens">Saldogrens:</label>
	<input type="text" id="saldogrens" name="saldogrens" value="{$roodschopper->getSaldogrens()}" /> <span class="small">in euro's</span><br />

	<label for="doelgroep">Doelgroep:</label>
	<select name="doelgroep" id="doelgroep">
		<option value="leden" {if $roodschopper->getDoelgroep()=='leden'}selected="selected"{/if}>Leden</option>
		<option value="oudleden" {if $roodschopper->getDoelgroep()=='oudleden'}selected="selected"{/if}>Oudleden en nobodies</option>
	</select><br />

	<label for="uitsluiten">Geen mail naar:</label>
	<input type="text" id="uitsluiten" name="uitsluiten" value="{$roodschopper->getUitgesloten()}" /> <span class="small">uid's gescheiden door een komma</span><br /><br />

	<label for="onderwerp">Onderwerp:</label>
	<input type="text" id="onderwerp" name="onderwerp" value="{$roodschopper->getOnderwerp()}" /><br />
	
	<div id="berichtContainer">
		<div id="berichtPreview" class="bbcodePreview"></div>
	</div>
	<label for="berichtInvoer">Mailbericht:<br /><br />
	<em class="small">Variabelen:<br />
	LID = Naam van lid<br />
	SALDO = Saldo van lid</em></label>
	<textarea name="bericht" id="berichtInvoer" rows="10" cols="80" style="resize:both;">{$roodschopper->getBericht()}</textarea><br />
	
	<div id="submitContainer">
		<label for="submit">&nbsp;</label>
		<a href="/wiki/cie:diensten:forum" target="_blank">Opmaakhulp</a>
		<input type="button" name="submit" id="submit" value="Verder gaan" onclick="roodschopper('simulate'); return false;" />
		<input id="forumVoorbeeld" type="button" onclick="window.bbcode.CsrBBPreview('berichtInvoer', 'berichtPreview')" value="Voorbeeld"/>
	</div>
	<div id="messageContainer" class="verborgen"></div>
	
	</fieldset>
</form>
<br />
<br />
{* TODO: dit ding met javascript mee laten veranderen met het kiezen van een commissie *}
<p>Kijk aan, hier doen we het voor!</p>
<!-- Stuk! <img src="/tools/saldografiek.php?uid=000&timespan=100&{$roodschopper->getCommissie()}" />-->
