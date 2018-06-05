{*
	mijn_voorkeuren.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<p>
Op deze pagina kunt u voorkeuren in- en uitschakelen voor periodieke maaltijden op Confide door op de knop te klikken in de kolom "Voorkeur".
Onderstaande tabel toont alle voorkeuren die u aan of uit kunt zetten.
De kolom "Voorkeur" geeft aan of uw voorkeur is ingeschakeld met "Ja", of is uitgeschakeld met "Nee".
</p>
<h3>Allergie en diëet</h3>
<p>
Het is mogelijk dat u allergisch bent voor bepaalde ingredienten, of dat u uit bepaalde overwegingen geen vlees wilt eten.
Dit kunt u hieronder aangeven, de koks zullen er dan rekening mee houden.
</p>
<p>
N.B. Dit is niet de plek om aan te geven dat u iets niet lekker vindt!
</p>
{$eetwens->view()}
<br />
<table id="maalcie-tabel" class="maalcie-tabel">
	<thead>
		<tr>
			<th>Voorkeur</th>
			<th>Functie</th>
			<th>Dag v/d week</th>
			<th>Periode</th>
		</tr>
	</thead>
	<tbody>
{foreach from=$voorkeuren item=voorkeur}
	{include file='maalcie/voorkeur/mijn_voorkeur_lijst.tpl' voorkeur=$voorkeur}
{/foreach}
	</tbody>
</table>