{getMelding()}

<ul class="nav nav-tabs">
	<li class="nav-item">
		<a href="/courant" class="nav-link active">Courantinzendingen</a>
	</li>
	<li class="nav-item">
		<a href="/courant/archief" class="nav-link">Archief</a>
	</li>
</ul>

<h1>C.S.R.-courant</h1>
<p>
	De C.S.R.-courant wordt elke maandagavond verzonden naar alle leden van C.S.R..
	Als u uw bericht voor 22:00 invoert, kunt u tamelijk zeker zijn van plaatsing in de courant.
	De PubCie streeft ernaar de courant rond 23:00/24:00 bij u in uw postvak te krijgen.
</p>
{if $berichten->rowCount() > 0}
	<div id="courantKnoppenContainer">
      {if $courant->magVerzenden()}
				<a href="/courant/verzenden" title="De C.S.R.-courant wilt versturen?" class="btn btn-primary post confirm">Verzenden</a>
				<a href="/courant/voorbeeld" class="btn btn-primary" target="_blank">Laat voorbeeld zien</a>
      {/if}
	</div>
    {* geen overzicht van berichten bij het bewerken... *}
	<h3>Overzicht van berichten:</h3>
	<dl>
      {foreach from=$berichten item=bericht}
				<dt>
					<span class="onderstreept">{$bericht->categorie|replace:'csr':'C.S.R.'}</span>
            {if $courant->magBeheren()}({CsrDelft\model\ProfielModel::getLink($bericht->uid, 'civitas')}){/if}
					<span class="dikgedrukt">{$bericht->titel}</span>
            {if $courant->magBeheren($bericht->uid)}
							<a class="btn btn-primary" href="/courant/bewerken/{$bericht->id}">bewerken</a>
							<a class="btn btn-primary post confirm ReloadPage" href="/courant/verwijderen/{$bericht->id}"
								 title="Bericht verwijderen">verwijderen</a>
            {/if}
				</dt>
				<dd id="courantbericht{$bericht->id}"></dd>
          {if !$courant->magBeheren($bericht->uid)}
						<dd>{$bericht->bericht|bbcode:"mail"}</dd>{/if}
      {/foreach}
	</dl>
{/if}

{$form->view()}

{*	</div>*}
{*</form>*}
{*{if $courant->magBeheren() AND $courant->getBerichtenCount()>0}<br />*}
{*{/if}*}
