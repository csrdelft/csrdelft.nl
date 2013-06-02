{*
 * Toon de catalogus
 *}
<ul class="horizontal">
	<li class="active">
		<a href="/communicatie/bibliotheek/" title="Naar de catalogus">Catalogus</a>
	</li>
	<li>
		<a href="/communicatie/bibliotheek/wenslijst" title="Wenslijst van bibliothecaris">Wenslijst</a>
	</li>
	{if $loginlid->hasPermission('P_BIEB_READ')}
		<li>
			<a href="/communicatie/bibliotheek/rubrieken" title="Rubriekenoverzicht">Rubrieken</a>
		</li>
	{/if}
</ul>
{if $loginlid->hasPermission('P_BIEB_READ')}
	<div class="controls">
		<a class="knop" href="/communicatie/bibliotheek/nieuwboek" title="Nieuw boek toevoegen">{icon get="book_add"} Boek toevoegen</a>
	</div>
{/if}

<h1>Catalogus van de C.S.R.-bibliotheek</h1>
<div class="foutje">{$melding}</div>
<p class="biebuitleg">
    Zoek hier in de boeken van de C.S.R.-bieb én van leden.<br>
    Je kunt je eigen boeken ook toevoegen en bijhouden aan wie of van wie jij boeken leent.
</p>
<br/>

{if $loginlid->hasPermission('P_BIEB_READ')}
		<div id="filters">
			<span id="alle" class="filter button">Alle</span><span id="csr" class="filter actief">C.S.R.</span><span id="leden" class="filter button">Leden</span><span id="eigen" class="filter button">Eigen</span><span id="geleend" class="filter button">Geleende boeken</span>
			<input id="boekstatus" type="checkbox" name="boekstatus" value="boekstatus"  /> <label for="boekstatus">Eigenaar en lener weergeven</label>
		</div>
{else}
	Log in om meer informatie van de boeken te bekijken.
{/if}

{if $loginlid->hasPermission('P_BIEB_READ')}
	<table id="boekencatalogus" class="boeken lid">
		<thead>
			<tr><th>Titel</th><th>Auteur</th><th>Rubriek</th><th title="Aantal recensies">#Rc.</th><th>Eigenaar</th><th>Uitgeleend&nbsp;aan</th><th>Uitleendatum</th></tr>
		</thead>
		<tbody>
			<tr><td colspan="7">Inhoud komt eraan2..</td></tr>
		</tbody>
		<tfoot>
			<tr><th>Titel</th><th>Auteur</th><th>Rubriek</th><th title="Aantal recensies">#Rc.</th><th>Eigenaar</th><th>Uitgeleend&nbsp;aan</th><th>Uitleendatum</th></tr>
		</tfoot>
	</table>
{else}
	<table id="boekencatalogus" class="boeken">
		<thead>
			<tr><th>Titel</th><th>Auteur</th><th>Rubriek</th></tr>
		</thead>
		<tbody>
			<tr><td colspan="3">Inhoud komt eraan..</td></tr>
		</tbody>
		<tfoot>
			<tr><th>Titel</th><th>Auteur</th><th>Rubriek</th></tr>
		</tfoot>
	</table>
{/if}


