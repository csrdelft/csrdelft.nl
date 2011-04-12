<form action="/actueel/groepen/{$groep->getType()->getNaam()}/{$groep->getId()}/bewerken" method="post">
<div id="groepFormulier" class="clear">
	{if $groep->isAdmin() OR $groep->isEigenaar()}
		{* alleen Korte naam invullen bij een nieuwe groep als de korte naam niet bekend is. Admins mogen wel aanpassen.*}
		{if $groep->getId()==0 AND ( !isset($oudegroep) OR $groep->isAdmin())}
			<h2>Nieuwe groep toevoegen in context {$groep->getType()->getNaam()}</h2>
			<br />
			<label for="groepSnaam"><strong>Korte naam:</strong></label>
			<div id="groepSnaam" class="opmerking">
				Voor gebruik in urls &eacute;n ter sortering. Alleen letters en cijfers, geen spaties. Voor elkaar opvolgende groepen dezelfde naam gebruiken.<br />
			</div>
			<input type="text" maxlength="20" name="snaam" value="{$groep->getSnaam()|escape:'html'}" />
		{else}
			<hr />
		{/if}
		<label for="groepNaam" class="clear"><strong>Naam:</strong></label>
		<input type="text" id="groepNaam" maxlength="50" name="naam" style="width: 70%" value="{$groep->getNaam()|escape:'html'}" /><br />
	
		{if $groep->isAdmin()}
			<label for="eigenaar" class="clear"><strong>Groepseigenaar:</strong></label>
			<input type="text" id="eigenaar" maxlength="255" name="eigenaar" style="width: 20%" value="{$groep->getEigenaar()|escape:'html'}" /><br />
		{/if}
		{if $groep->getType()->getId()==11 AND !$groep->isAdmin()}
			<input type="hidden" id="groepStatus" name="status" value="ht" />
		{else}
			<label for="groepStatus"><strong>Status:</strong></label>
			<select name="status" id="groepStatus" onchange="updateGroepform();">
				<option value="ht" {if $groep->getStatus()=="ht"}selected="selected"{/if}>h.t.</option>
				<option value="ot" {if $groep->getStatus()=="ot"}selected="selected"{/if}>o.t.</option>
				<option value="ft" {if $groep->getStatus()=="ft"}selected="selected"{/if}>f.t.</option>
			</select><br />
		{/if}
		
		<label for="begin"><strong>Periode:</strong></label> 
		<input type="text" id="begin" name="begin" value="{$groep->getBegin()}" /> - <input type="text" name="einde" id="einde" value="{$groep->getEinde()}" />
		<div id="einde" class="opmerking" style="width: 295px">
			Op einddatum en later is aanmelden gesloten.<br />
		</div>
		<br />
		<hr />
		<div id="groepAanmeldbaarContainer" style="display: none;">
		{if $groep->getType()->getId()==11 AND ($groep->getId()==0 OR !$groep->isAdmin())}
			<input type="hidden" id="groepAanmeldbaar" name="aanmeldbaar" value="niet-leeg-zodat-js-aanmeldopties-weergeeft" />
		{else}
				<label for="groepAanmeldbaar"><strong>Aanmeldbaar?</strong></label>
				<select name="aanmeldbaar" id="groepAanmeldbaar" onchange="updateGroepform();"  /> 
				{foreach from=$aanmeldfilters key=filtervalue item=filtertekst}
					<option value="{$filtervalue}" {if $filtervalue==$groep->getAanmeldbaar()}selected="selected"{/if}>
						{$filtertekst}
					</option>
				{/foreach}
				</select>
			{/if}
		</div>
		<div id="groepLimietContainer" style="display: none;">
			<label for="groepLimiet"><strong>Limiet:</strong></label>
			<input type="input" name="limiet" id="groepLimiet" value="{$groep->getLimiet()}" /> <em>Vul een 0 voor geen limiet.</em>
		</div>
		<label for="toonFuncties"><strong>Toon opmerkingen?</strong></label>
		<div id="functieOpmVerbergen" class="opmerking verborgen">Opmerkingen zijn verborgen voor leden, ze kunnen wel opgegeven worden.</div>
			<div id="functieOpmNiet" class="opmerking verborgen">Er kunnen nu geen opmerkingen worden opgegeven.</div>
			<div id="functieOpmTonenzonderinvoer" class="opmerking verborgen">Opmerkingen altijd zichtbaar, maar ze kunnen niet opgegeven worden.</div>
		<select name="toonFuncties" id="toonFuncties" onchange="updateGroepform();">
			<option value="tonen" {if $groep->getToonFuncties()=="tonen" AND !($groep->getId()==0 AND $groep->getType()->getId()==11)}selected="selected"{/if}>Altijd</option>
			<option value="tonenzonderinvoer" {if $groep->getToonFuncties()=="tonenzonderinvoer"}selected="selected"{/if}>Altijd. Maar geen invoer.</option>
			<option value="verbergen" {if $groep->getToonFuncties()=="verbergen"}selected="selected"{/if}>Alleen voor groepadmins</option>
			<option value="niet" {if $groep->getToonFuncties()=="niet" OR ($groep->getId()==0 AND $groep->getType()->getId()==11)}selected="selected"{/if}>Nooit</option>
		</select><br />
		<label for="functiefilter"><strong>Opmerkingfilter:</strong></label>
		<input type="text" name="functiefilter" value="{$groep->getFunctiefilter()|escape:'html'}" /><div class="opmerking">Door '|' gescheiden mogelijke opties (eerste is de standaard). Veld leeglaten voor vrije invoer.</div>
		
		<hr class="clear" />
		<label for="toonPasfotos"><strong>Toon pasfoto's?</strong></label>
		<input type="checkbox" name="toonPasfotos" id="toonPasfotos" {if $groep->getToonPasfotos()}checked="checked"{/if} /> <em>(Pasfoto komt in plaats van naam)</em>
		<br />
		{if $groep->getType()->getId()!=11 OR $groep->isAdmin()}
			<label for="lidIsMod"><strong>Groepslid is mod?</strong></label>
			<input type="checkbox" name="lidIsMod" id="lidIsMod" {if $groep->getlidIsMod()}checked="checked"{/if} /> <em>(Elk lid kan groepsleden toevoegen en het grote verhaal aanpassen.)</em>
			<br />
		{/if}
		<label for="sbeschrijving"><strong>Korte beschrijving:</strong><br /><br />UBB staat aan.</label>
		<textarea id="sbeschrijving" name="sbeschrijving" style="width: 70%; height: 100px;">{$groep->getSbeschrijving()|escape:'html'}</textarea>
		<br />
	{/if}
	<label for="sbeschrijving"><strong>Lange beschrijving:</strong><br /><br />UBB staat aan.</label>
	<textarea id="sbeschrijving" name="beschrijving" style="width: 70%; height: 200px;">{$groep->getBeschrijving()|escape:'html'}</textarea><br />
	<hr />
	<label for="submit"></label><input type="submit" id="submit" value="Opslaan" /> <a href="/actueel/groepen/{$groep->getType()->getNaam()}/{$groep->getId()}/" class="knop">terug</a>
</div>
</form>
<script type="text/javascript">
	updateGroepform();
</script>
