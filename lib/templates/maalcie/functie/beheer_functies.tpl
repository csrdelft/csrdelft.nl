{*
	beheer_functies.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<p>
Op deze pagina kunt u corveefuncties aanmaken, wijzigen en verwijderen.
Onderstaande tabel toont alle functies in het systeem.
Ook kunt u aangeven of er een kwalificatie benodigd is en een kwalificatie toewijzen of intrekken.
</p>
<p>
N.B. Voordat een corveefunctie verwijderd kan worden moeten eerst alle bijbehorende corveetaken en alle bijbehorende corveerepetities definitief zijn verwijderd.
</p>
<div class="float-right"><a href="/corvee/functies/toevoegen" class="btn post popup">{icon get="add"} Nieuwe functie</a></div>
<table id="maalcie-tabel" class="maalcie-tabel">
	<thead>
		<tr>
			<th>Wijzig</th>
			<th title="Afkorting">Afk</th>
			<th>Naam</th>
			<th>Standaard<br />punten</th>
			<th title="Email bericht">{icon get="email"}</th>
			<th>Gekwalificeerden</th>
			<th title="Mag maaltijden sluiten">{icon get="lock_add"}</th>
			<th title="Definitief verwijderen" class="text-center">{icon get="cross"}</th>
		</tr>
	</thead>
	<tbody>
{foreach from=$functies item=functie}
	{include file='maalcie/functie/beheer_functie_lijst.tpl'}
{/foreach}
	</tbody>
</table>
