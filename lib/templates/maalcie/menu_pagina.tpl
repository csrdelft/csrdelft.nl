{* menu_pagina.tpl	|	P.W.G. Brussee (brussee@live.nl) *}
<div id="maalcie-menu">
	<ul class="horizontal">
		<li><a href="/maaltijden/ketzer">Maaltijdenketzer</a></li>
		<li><a href="/maaltijden/abonnementen">Mijn abonnementen</a></li>
		<li><a href="/corvee/rooster">Corveerooster</a></li>
		<li><a href="/corvee">Mijn corveeoverzicht</a></li>
		<li><a href="/corvee/voorkeuren">Mijn voorkeuren</a></li>
{toegang 'P_MAAL_MOD,P_CORVEE_MOD'}
		<li>
			<select onchange="window.location.href = this.value;">
				<option value="">Beheermenu</option>
				<optgroup label="Maaltijden">
					<option value="/maaltijden/beheer">Beheer</option>
					<option value="/maaltijden/beheer/prullenbak">Prullenbak</option>
					<option value="/maaltijden/beheer/archief">Archief</option>
					<option value="/maaltijden/beheer/beoordelingen">Beoordelingen</option>
					<option value="/maaltijden/repetities">Repetities</option>
					<option value="/instellingenbeheer/module/maaltijden">Instellingen</option>
					<option value="/maaltijden/abonnementenbeheer">Abonnementen</option>
				</optgroup>
				<optgroup label="Fiscaat">
					<option value="/fiscaat/producten">Productbeheer</option>
					<option value="/fiscaat/saldo">Saldobeheer</option>
					<option value="/maaltijden/fiscaat/onverwerkt">Onverwerkt</option>
					<option value="/maaltijden/boekjaar">Boekjaar sluiten</option>
				</optgroup>
				<optgroup label="Corvee">
					<option value="/corvee/beheer">Beheer</option>
					<option value="/corvee/functies">Functies & kwalificaties</option>
					<option value="/instellingenbeheer/module/corvee">Instellingen</option>
					<option value="/corvee/puntenbeheer">Punten</option>
					<option value="/corvee/voorkeurenbeheer">Voorkeuren</option>
					<option value="/corvee/vrijstellingen">Vrijstellingen</option>
				</optgroup>
			</select>
		</li>
{/toegang}
	</ul>
</div>
<hr/>
<table>
	<tr id="maalcie-melding">
		<td id="maalcie-melding-veld">{getMelding()}</td>
	</tr>
</table>
{if isset($titel)}
	<h1>{$titel}</h1>
{/if}
