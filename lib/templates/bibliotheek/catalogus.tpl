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
</ul>
{if $loginlid->hasPermission('P_BIEB_READ')}
	<div class="controls">
		<a class="knop" href="/communicatie/bibliotheek/nieuwboek" title="Nieuw boek toevoegen">{icon get="book_add"} Boek toevoegen</a>
	</div>
{/if}

<h1>Catalogus van de C.S.R.-bibliotheek</h1>
<div class="foutje">{$melding}</div>
<br/>

{if $loginlid->hasPermission('P_BIEB_READ')}
	<div id="filters">
		Selecteer: 	<input id="alle" type="radio" name="filter-catalogus" value="alle"> <label for="alle">Alle</label> 
					<input id="csr"  type="radio" name="filter-catalogus" value="csr" checked> <label for="csr">C.S.R.</label> 
					<input id="leden" type="radio" name="filter-catalogus" value="leden"> <label for="leden">Leden</label>
					<input id="eigen" type="radio" name="filter-catalogus" value="eigen"> <label for="eigen">Eigen</label>
					<input id="geleend" type="radio" name="filter-catalogus" value="geleend"> <label for="geleend">Geleende boeken</label>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input id="boekstatus" type="checkbox" name="boekstatus" value="boekstatus"  /> <label for="boekstatus">Boekstatus weergeven</label>

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


