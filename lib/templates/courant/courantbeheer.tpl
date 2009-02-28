<div style="float: right; margin: 0 0 10px 10px;">
	<a href="/actueel/courant/archief/" title="Archief">Archief</a>
</div>
<h1>C.S.R.-courant</h1>
<p>
	De C.S.R.-courant wordt elke woensdagmiddag verzonden naar alle leden van C.S.R.. 
	Als u uw bericht voor 17:00 invoert, kunt u tamelijk zeker zijn van plaatsing in de courant.
	De PubCie streeft ernaar de courant voor 18:00 bij u in uw postvak te krijgen.
</p>
<div id="knoppenContainer">
	{if $courant->magVerzenden()}
		<a href="/actueel/courant/verzenden.php" onclick="return confirm('Weet u het zeker dat u de C.S.R.-courant wilt versturen?')" class="knop">Verzenden</a>
	{/if}
	{* Volgens mij wordt deze nooit gebruikt...
		{if $courant->magBeheren()}
		<a href="/actueel/courant/leegmaken" class="knop" onclick="return confirm('Weet u zeker dat u de cache wilt leeggooien?')">Leegmaken</a>
		{/if}
	*}
</div>

{* feutmeldingen weergeven... *}
{if $melding!=''}{$melding}{/if}

{* geen overzicht van berichten bij het bewerken... *}
{if $form.ID==0}
	<h3>Overzicht van berichten:</h3>
	{if !is_array($courant->getBerichtenVoorGebruiker())}
		U heeft nog geen berichten geplaatst in deze C.S.R.-courant.
	{else}
		<dl>
			{foreach from=$courant->getBerichtenVoorGebruiker() item=bericht}
				<dt>
					<u>{$bericht.categorie|replace:'csr':'C.S.R.'}</u>
					{if $courant->magBeheren()}({$bericht.uid|csrnaam:'full':false}){/if}
					<strong>{$bericht.titel}</strong>
					[ <a href="/actueel/courant/bewerken/{$bericht.ID}">bewerken</a> | 
					<a href="/actueel/courant/verwijder/{$bericht.ID}" onclick="return confirm('Weet u zeker dat u dit bericht wilt verwijderen?')" >verwijderen</a> ]
				</dt>
				<dd id="courantbericht{$bericht.ID}"></dd>
				{if !$courant->magBeheren()}<dd>{$bericht.bericht|ubb}</dd>{/if}
			{/foreach}
		</dl>
			
	{/if}
{/if}


<form method="post" action="?ID={$form.ID}" >
	<div id="pubciemail_form">
		<h3>{if $form.ID==0}Nieuw bericht invoeren{else}Bericht bewerken{/if}</h3><br />
		<strong>Titel:</strong><br />
		<input type="text" name="titel" value="{$form.titel|escape:'html'}" style="width: 100%;" class="tekst" />
		<br /><br />
		<strong>Categorie:</strong><br />
		Selecteer hier een categorie. Uw invoer is enkel een voorstel.
		<em>Aankondigingen over kamers te huur komen in <strong>overig</strong> terecht! C.S.R. is bedoeld voor 
		activiteiten van C.S.R.-commissies en andere verenigingsactiviteiten.</em><br />
		{html_options name=categorie values=$courant->getCats() output=$courant->getCats(true) selected=$form.categorie}
		<br /><br />
		<strong>Bericht:</strong><br />
		<div id="bewerkPreviewContainer" class="previewContainer"><div id="bewerkPreview" class="preview"></div></div>
		<textarea name="bericht" id="courantBericht" cols="80" style="width: 100%;" rows="15" class="tekst">{$form.bericht|escape:'html'}</textarea>
		<a style="float: right;" class="handje knop" onclick="toggleDiv('ubbhulpverhaal')" title="Opmaakhulp weergeven">UBB</a>
		<a style="float: right;" class="handje knop" onclick="vergrootTextarea('courantBericht', 10)" title="Vergroot het invoerveld"><strong>&uarr;&darr;</strong></a>
		<input type="submit" name="verzenden" value="opslaan" class="tekst" /> 
		<input type="button" value="voorbeeld" onclick="return previewPost('courantBericht', 'bewerkPreview')" />
	</div>
</form>
{if $courant->magBeheren()}<br />
	<h3>Voorbeeld van de C.S.R.-courant.</h3>
		<script type="text/javascript">//<![CDATA[{literal}
			function showIframe(){
				target =document.getElementById('courant_voorbeeld');
				target.innerHTML = "<iframe src=\"/actueel/courant/courant.php\" style=\"width: 100%; height: 600px;\"></iframe>";
			}
		//]]></script>{/literal}
		<a href="#" onclick="showIframe()">Laat voorbeeld zien...</a>
		<div id="courant_voorbeeld"></div>
{/if}
