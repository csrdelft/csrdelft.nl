{*
 * Toon de beheerlijst
 *}
<ul class="horizontal">
	<li >
		<a href="/communicatie/bibliotheek/" title="Naar de catalogus">Catalogus</a>
	</li>
	<li class="active">
		<a href="/communicatie/bibliotheek/beheer" title="Naar de beheeroverzicht">Beheer</a>
	</li>
</ul>
{if $loginlid->hasPermission('P_BIEB_READ')}
	<div class="controls">
		<a class="knop" href="/communicatie/bibliotheek/nieuwboek">{icon get="book_add"} Toevoegen</a>
	</div>
{/if}

<h1>Overzichten</h1>
<div class="foutje">{$melding}</div>

<div id="filters">
	Selecteer: <a {if $catalogus->getFilter()=='alle'}class="actief"{/if} href="/communicatie/bibliotheek/beheer/alle">Alle boeken</a> - 
	<a {if $catalogus->getFilter()=='csr'}class="actief"{/if} href="/communicatie/bibliotheek/beheer/csr">C.S.R. boeken</a> - 
	<a {if $catalogus->getFilter()=='leden'}class="actief"{/if} href="/communicatie/bibliotheek/beheer/leden">Leden boeken</a> - 
	<a {if $catalogus->getFilter()=='eigen'}class="actief"{/if} href="/communicatie/bibliotheek/beheer/eigen">Eigen boeken</a> - 
	<a {if $catalogus->getFilter()=='geleend'}class="actief"{/if} href="/communicatie/bibliotheek/beheer/geleend">Geleende boeken</a> 
</div>
<table id="boekenbeheerlijsten" class="boeken">
	<thead>
		<tr><th>Titel</th><th>Titel2</th><th>Auteur</th><th>Rubriek</th><th>Code</th><th>ISBN</th><th title="Aantal beschrijvingen">#recensies</th><th>Boekeigenaar&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th><th>Uitgeleend&nbsp;aan:</th><th>Status</th></tr>
	</thead>
	<tbody>
	{foreach from=$catalogus->getBeheerBoeken() item=boek}
		<tr class="document">
			<td>
				<a href="/communicatie/bibliotheek/boek/{$boek.bkid}" 
				title="Boek: {$boek.titel|escape:'html'} 
Auteur: {$boek.auteur} 
Rubriek: {$boek.categorie}">
					{$boek.titel|escape:'html'|truncate:40:"…"}
				</a>
			</td>
			<td class="titel">{$boek.titel}</td>
			<td class="auteur">{$boek.auteur}</td>
			<td class="rubriek">{$boek.categorie}</td>
			<td class="code">{$boek.code}</td>
			<td class="isbn">{$boek.isbn}</td>
			<td class="aantal">{$boek.bsaantal}</td>
			<td class="eigenaar">
				{foreach from=$boek.exemplaren item=exemplaar}
					{if $exemplaar.eigenaar=='x222'}C.S.R.-bibliotheek{elseif $exemplaar.eigenaar==''}-{else}{$exemplaar.eigenaar|csrnaam:'civitas'}{/if}<br />
				{/foreach}
			</td>
			<td class="lener">
				{foreach from=$boek.exemplaren item=exemplaar}
					{if $exemplaar.lener==''}-{else}{$exemplaar.lener|csrnaam:'civitas'}{/if}<br />
				{/foreach}
			</td>
			<td class="status">
				{foreach from=$boek.exemplaren item=exemplaar}
					{$exemplaar.status}<br />
				{/foreach}
			</td>
		</tr>
	{foreachelse}
		Geen boeken
	{/foreach}
	</tbody>
	<tfoot>
		<tr><th>Titel</th><th>Titel2</th><th>Auteur</th><th>Rubriek</th><th>Code</th><th>ISBN</th><th title="Aantal beschrijvingen">#bs</th><th>Boekeigenaar&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th><th>Uitgeleend&nbsp;aan:</th><th>Status</th></tr>
	</tfoot>
</table>

