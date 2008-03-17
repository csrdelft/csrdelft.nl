<form action="/groepen/{$gtype}/{$groep->getId()}/bewerken" method="post">
<div id="groepFormulier" class="clear">
	{if $groep->isAdmin()}
		{if $groep->getId()==0}
			<h2>Nieuwe groep toevoegen in context {$gtype}</h2>
			
			<label for="groepSnaam"><strong>Korte naam:</strong></label>
			<div id="groepSnaam" class="opmerking">
				Voor gebruik in urls &eacute;n ter sortering. Alleen letters en cijfers, geen spaties. Voor elkaar opvolgende groepen dezelfde naam gebruiken.<br />
			</div>
			<input type="text"  name="snaam" value="{$groep->getSnaam()|escape:'html'}" />
			
		{/if}
		<div class="clear"></div>
		<label for="groepNaam"><strong>Naam:</strong></label>
		<input type="text" id="groepNaam" name="naam" style="width: 70%" value="{$groep->getNaam()|escape:'html'}" /><br />

		<label for="groepStatus"><strong>Status:</strong></label>
		<select name="status" id="groepStatus" onchange="updateGroepform();">
			<option value="ht" {if $groep->getStatus()=="ht"}selected="selected"{/if}>h.t.</option>
			<option value="ot" {if $groep->getStatus()=="ot"}selected="selected"{/if}>o.t.</option>
			<option value="ft" {if $groep->getStatus()=="ft"}selected="selected"{/if}>f.t.</option>
		</select><br />
		
		<label for="begin"><strong>Periode:</strong></label> 
		<input type="text" id="begin" name="begin" value="{$groep->getBegin()}" /> - <input type="text" name="einde" value="{$groep->getEinde()}" />
		<br />
		<div id="groepAanmeldbaarContainer" style="display: none;">
			<label for="groepAanmeldbaar"><strong>Aanmeldbaar?</strong></label>
			<input type="checkbox" name="aanmeldbaar" id="groepAanmeldbaar" onchange="updateGroepform();" {if $groep->isAanmeldbaar()}checked="checked"{/if} />
		</div>
		<div id="groepLimietContainer" style="display: none;">
			<label for="groepLimiet"><strong>Limiet:</strong></label>
			<input type="input" name="limiet" id="groepLimiet" value="{$groep->getLimiet()}" /> <em>Vul een 0 voor geen limiet.</em>
		</div>
		<label for="toonFuncties"><strong>Toon functies?</strong></label>
		<div id="functieOpmVerbergen" class="opmerking verborgen">Functies zijn verborgen voor leden, ze kunnen wel opgegeven worden.</div>
			<div id="functieOpmNiet" class="opmerking verborgen">Er kunnen nu geen functies worden opgegeven.</div>
		<select name="toonFuncties" id="toonFuncties" onchange="updateGroepform();">
			<option value="tonen" {if $groep->getToonFuncties()=="tonen"}selected="selected"{/if}>Altijd</option>
			<option value="verbergen" {if $groep->getToonFuncties()=="verbergen"}selected="selected"{/if}>Alleen voor groepadmins</option>
			<option value="niet" {if $groep->getToonFuncties()=="niet"}selected="selected"{/if}>Nooit</option>
		</select>
			
		<br />
		<label for="sbeschrijving"><strong>Korte beschrijving:</strong><br /><br />UBB staat aan.</label>
		<textarea id="sbeschrijving" name="sbeschrijving" style="width: 70%; height: 100px;">{$groep->getSbeschrijving()|escape:'html'}</textarea>
		<br />
	{/if}
	<label for="sbeschrijving"><strong>Lange beschrijving:</strong><br /><br />UBB staat aan.</label>
	<textarea id="sbeschrijving" name="beschrijving" style="width: 70%; height: 200px;">{$groep->getBeschrijving()|escape:'html'}</textarea><br />
	<label for="submit"></label><input type="submit" id="submit" value="Opslaan" /> <a href="/groepen/{$gtype}/{$groep->getId()}/" class="knop">terug</a>
</div>
</form>
<script type="text/javascript">
	updateGroepform();
</script>