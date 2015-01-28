{* menu_pagina.tpl	|	P.W.G. Brussee (brussee@live.nl) *}
<div id="maalcie-menu">
	<ul class="horizontal">
		<li><a href="/maaltijdenketzer">Maaltijdenketzer</a></li>
		<li><a href="/maaltijdenabonnementen">Mijn abonnementen</a></li>
		<li><a href="/corveerooster">Corveerooster</a></li>
		<li><a href="/corvee">Mijn corveeoverzicht</a></li>
		<li><a href="/corveevoorkeuren">Mijn voorkeuren</a></li>
		<li>
			<select onchange="window.location.href = this.value;">
				<option value="">Beheermenu</option>
				<optgroup label="Maaltijden">
					<option value="/maaltijdenbeheer">Beheer</option>
					<option value="/maaltijdenbeheer/archief">Archief</option>
					<option value="/instellingenbeheer/module/maaltijden">Instellingen</option>
					<option value="/maaltijdenabonnementenbeheer">Abonnementen</option>
					<option value="/maaltijdenmaalciesaldi">Saldi</option>
				</optgroup>
				<optgroup label="Corvee">
					<option value="/corveebeheer">Beheer</option>
					<option value="/corveefuncties">Functies & kwalificaties</option>
					<option value="/instellingenbeheer/module/corvee">Instellingen</option>
					<option value="/corveepuntenbeheer">Punten</option>
					<option value="/corveevoorkeurenbeheer">Voorkeuren</option>
					<option value="/corveevrijstellingen">Vrijstellingen</option>
				</optgroup>
			</select>
		</li>
	</ul>
</div>
<hr/>
<table>
	<tr id="maalcie-melding">
		<td id="maalcie-melding-veld">{getMelding()}</td>
	</tr>
</table>
<h1>{$titel}</h1>