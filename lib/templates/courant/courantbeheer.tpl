<ul class="horizontal nobullets">
	<li class="active">
		<a href="/actueel/courant/" title="Courantinzendingen">Courantinzendingen</a>
	</li>
	<li>
		<a href="/actueel/courant/archief/" title="Archief">Archief</a>
	</li>
</ul>
<hr />

{* feutmeldingen weergeven... *}
{getMelding()}

<h1>C.S.R.-courant</h1>
<p>
	De C.S.R.-courant wordt elke maandagavond verzonden naar alle leden van C.S.R.. 
	Als u uw bericht voor 22:00 invoert, kunt u tamelijk zeker zijn van plaatsing in de courant.
	De PubCie streeft ernaar de courant rond 23:00/24:00 bij u in uw postvak te krijgen.
</p>
<div id="knoppenContainer">
	{if $courant->magVerzenden() AND sizeof($courant->getBerichten())>0}
		<a href="/actueel/courant/verzenden.php" onclick="return confirm('Weet u het zeker dat u de C.S.R.-courant wilt versturen?')" class="knop">Verzenden</a>
	{/if}
	{* Volgens mij wordt deze nooit gebruikt...
	{if $courant->magBeheren()}
	<a href="/actueel/courant/leegmaken" class="knop" onclick="return confirm('Weet u zeker dat u de cache wilt leeggooien?')">Leegmaken</a>
	{/if}
	*}
</div>

{* geen overzicht van berichten bij het bewerken... *}
{if $form.ID==0 AND sizeof($courant->getBerichtenVoorGebruiker())>0}
	<h3>Overzicht van berichten:</h3>
	<dl>
		{foreach from=$courant->getBerichtenVoorGebruiker() item=bericht}
			<dt>
			<span class="onderstreept">{$bericht.categorie|replace:'csr':'C.S.R.'}</span>
			{if $courant->magBeheren()}({$bericht.uid|csrnaam:'full':false}){/if}
			<span class="dikgedrukt">{$bericht.titel}</span>
			[ <a href="/actueel/courant/bewerken/{$bericht.ID}">bewerken</a> | 
			<a href="/actueel/courant/verwijder/{$bericht.ID}" onclick="return confirm('Weet u zeker dat u dit bericht wilt verwijderen?')" >verwijderen</a> ]
			</dt>
			<dd id="courantbericht{$bericht.ID}"></dd>
			{if !$courant->magBeheren()}<dd>{$bericht.bericht|ubb}</dd>{/if}
		{/foreach}
	</dl>
{/if}

<form action="?ID={$form.ID}" method="post">
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
		<div id="bewerkPreview" class="preview"></div>
		<textarea name="bericht" id="courantBericht" cols="80" style="width: 100%;" rows="15" class="tekst">{$form.bericht|escape:'html'}</textarea>
		<a class="knop float-right" onclick="$('#ubbhulpverhaal').toggle();" title="Opmaakhulp weergeven">Opmaak</a>
		<a class="knop float-right vergroot" data-vergroot="#courantBericht" title="Vergroot het invoerveld">&uarr;&darr;</a>
		<input type="submit" name="verzenden" value="Opslaan" class="tekst" /> 
		<input type="button" value="Voorbeeld" onclick="ubbPreview('courantBericht', 'bewerkPreview');" />
		{if $courant->magBeheren()}
			<input type="button" value="Importeer agenda" onclick="importAgenda('courantBericht');" />
		{/if}
	</div>
</form>
{if $courant->magBeheren() AND $courant->getBerichtenCount()>0}<br />
	<h3 id="voorbeeld">Voorbeeld van de C.S.R.-courant.</h3>
	<script type="text/javascript">//<![CDATA[{literal}
		function showIframe() {
			target = document.getElementById('courant_voorbeeld');
			target.innerHTML = "<iframe src=\"/actueel/courant/courant.php\" style=\"width: 100%; height: 600px;\"></iframe>";
		}
		//]]></script>{/literal}
	<a href="#voorbeeld" onclick="showIframe()">Laat voorbeeld zien...</a>
	<div id="courant_voorbeeld"></div>
{/if}