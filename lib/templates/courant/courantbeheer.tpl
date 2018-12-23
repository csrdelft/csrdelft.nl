<ul class="horizontal nobullets">
	<li class="active">
		<a href="/courant/" title="Courantinzendingen">Courantinzendingen</a>
	</li>
	<li>
		<a href="/courant/archief/" title="Archief">Archief</a>
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
	{if $courant->magVerzenden()}
		<a href="/courant/verzenden" onclick="return confirm('Weet u het zeker dat u de C.S.R.-courant wilt versturen?')" class="btn">Verzenden</a>
	{/if}
</div>

{* geen overzicht van berichten bij het bewerken... *}
{if $form.ID==0 AND sizeof($courant->getBerichtenVoorGebruiker()) > 0}
	<h3>Overzicht van berichten:</h3>
	<dl>
		{foreach from=$courant->getBerichtenVoorGebruiker() item=bericht}
			<dt>
			<span class="onderstreept">{$bericht.categorie|replace:'csr':'C.S.R.'}</span>
			{if $courant->magBeheren()}({CsrDelft\model\ProfielModel::getLink($bericht.uid, 'civitas')}){/if}
			<span class="dikgedrukt">{$bericht.titel}</span>
			{if $courant->magBeheren($bericht.uid)}
				<a class="btn" href="/courant/bewerken/{$bericht.ID}">bewerken</a>
				<a class="btn" href="/courant/verwijderen/{$bericht.ID}" onclick="return confirm('Weet u zeker dat u dit bericht wilt verwijderen?')" >verwijderen</a>
			{/if}
			</dt>
			<dd id="courantbericht{$bericht.ID}"></dd>
			{if !$courant->magBeheren($bericht.uid)}<dd>{$bericht.bericht|bbcode:"mail"}</dd>{/if}
		{/foreach}
	</dl>
{/if}

<form action="/courant/{if $form.ID==0}toevoegen{else}bewerken/{$form.ID}{/if}" method="post">
	<div id="pubciemail_form">
		<h3>{if $form.ID==0}Nieuw bericht invoeren{else}Bericht bewerken{/if}</h3>
		<strong>Titel:</strong><br />
		<input type="text" name="titel" value="{$form.titel|escape:'html'}" class="breed" />
		<br /><br />
		<strong>Categorie:</strong><br />
		Selecteer hier een categorie. Uw invoer is enkel een voorstel.
		<em>Aankondigingen over kamers te huur komen in <strong>overig</strong> terecht! C.S.R. is bedoeld voor 
			activiteiten van C.S.R.-commissies en andere verenigingsactiviteiten.</em><br />
			{html_options name=categorie values=$courant->getCats() output=$courant->getCats(true) selected=$form.categorie}
		<br /><br />
		<strong>Bericht:</strong> <em>(alleen [url] en [img] werken in de email)</em><br />
		<div id="bewerkPreview" class="bbcodePreview"></div>
		<textarea id="courantBericht" name="bericht" class="breed" rows="15" style="resize:vertical;">{$form.bericht|escape:'html'}</textarea>
		<input type="submit" name="verzenden" value="Opslaan" /> 
		<input type="button" value="Voorbeeld" onclick="window.bbcode.CsrBBPreview('courantBericht', 'bewerkPreview');" />
		{if $courant->magBeheren()}
			<input type="button" value="Importeer agenda" onclick="window.courant.importAgenda('courantBericht');" />
			<input type="button" value="Importeer sponsor" onclick="document.getElementById('courantBericht').value += '[img]https://csrdelft.nl/plaetjes/banners/Projectkwadraat.jpg[/img]'" />
		{/if}
	</div>
</form>
{if $courant->magBeheren() AND $courant->getBerichtenCount()>0}<br />
	<a href="/courant/voorbeeld">Laat voorbeeld zien</a>
{/if}
