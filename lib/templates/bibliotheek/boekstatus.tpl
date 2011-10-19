{*
 * Toon de boekstatuslijst
 *}
<ul class="horizontal">
	<li >
		<a href="/communicatie/bibliotheek/" title="Naar de catalogus">Catalogus</a>
	</li>
	<li class="active">
		<a href="/communicatie/bibliotheek/boekstatus" title="Uitgebreide boekstatus">Boekstatus</a>
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

<h1>Boekstatus</h1>
<div class="foutje">{$melding}</div>
<br/>
<div id="filters">
	Selecteer: 
	<input id="alle" type="radio" name="filter-boekstatus" value="alle"><label for="alle">Alle boeken</label> 
	<input id="csr"  type="radio" name="filter-boekstatus" value="csr" checked><label for="csr">C.S.R.-boeken</label> 
	<input id="leden" type="radio" name="filter-boekstatus" value="leden"><label for="leden">Boeken van Leden</label>
	<input id="eigen" type="radio" name="filter-boekstatus" value="eigen"><label for="eigen">Eigen boeken</label>
	<input id="geleend" type="radio" name="filter-boekstatus" value="geleend"><label for="geleend">Geleende boeken</label>
</div>

<table id="boekenbeheerlijsten" class="boeken">
	<thead>
		<tr><th>Titel</th><th>Code</th><th title="Aantal beschrijvingen">#recensies</th><th>Boekeigenaar&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th><th>Uitgeleend&nbsp;aan</th><th>Status</th><th title="Hoevaak is exemplaar uitgeleend?">#leningen</th></tr>
	</thead>
	<tbody>
		<tr><td colspan="7">Inhoud komt eraan..</td></tr>
	</tbody>
	<tfoot>
		<tr><th>Titel</th><th>Code</th><th title="Aantal beschrijvingen">#recensies</th><th>Boekeigenaar&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th><th>Uitgeleend&nbsp;aan</th><th>Status</th><th title="Hoevaak is exemplaar uitgeleend?">#leningen</th></tr>
	</tfoot>
</table>

