{*
	beheer_maaltijd_repetities.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<p>
Op deze pagina kunt u de repetitiespatronen voor maaltijden aanmaken, wijzigen en verwijderen.
Onderstaande tabel toont alle repetities.
</p>
<h3>Repetities verwijderen</h3>
<p>
Voordat een maaltijdrepetitie verwijderd kan worden moeten eerst alle bijbehorende maaltijden definitief zijn verwijderd.
Dit is dus inclusief alle maaltijdaanmeldingen (die mogelijk door een abonnement op deze maaltijdrepetitie zijn aangemaakt).
Bij het verwijderen van een maaltijdrepetitie zullen eerst deze bijbehorende maaltijden in de prullenbak worden geplaatst als ze er zijn.
Daarnaast blijven bij het verwijderen eventuele gekoppelde corveerepetities bestaan.
</p>
<p>
N.B. Pas na het definitief verwijderen van de bijbehorende maaltijden zal de maaltijdrepetitie verwijderd kunnen worden.
Dan pas zullen ook alle abonnementen op deze maaltijdrepetitie automatisch worden uitgeschakeld en verwijderd.
</p>
<div class="float-right"><a href="/maaltijden/repetities/nieuw" class="btn post popup">{icon get="add"} Nieuwe repetitie</a></div>
<table id="maalcie-tabel" class="maalcie-tabel">
	<thead>
		<tr>
			<th>Wijzig</th>
			<th>Titel</th>
			<th>Dag</th>
			<th>Periode</th>
			<th>Tijd</th>
			<th>Prijs</th>
			<th>Limiet</th>
			<th>{icon get="tick" title="Abonneerbaar"}</th>
			<th>Filter</th>
			<th title="Definitief verwijderen" class="text-center">{icon get="cross"}</th>
		</tr>
	</thead>
	<tbody>
{foreach from=$repetities item=repetitie}
	{include file='maalcie/maaltijd-repetitie/beheer_maaltijd_repetitie_lijst.tpl' repetitie=$repetitie}
{/foreach}
	</tbody>
</table>
