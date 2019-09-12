{*
	beheer_vrijstellingen.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<p>
Op deze pagina kunt u vrijstellingen aanmaken, wijzigen en verwijderen. Onderstaande tabel toont alle vrijstellingen in het systeem.
</p>
<p>
N.B. Pas bij het resetten van het corveejaar worden de punten toegekend (te behalen corveepunten per jaar maal het vrijstellingspercentage afgerond naar boven).
</p>
<div class="float-right"><a href="/corvee/vrijstellingen/nieuw" class="btn post popup">{icon get="add"} Nieuwe vrijstelling</a></div>
<table id="maalcie-tabel" class="maalcie-tabel">
	<thead>
		<tr>
			<th>Wijzig</th>
			<th>Lid</th>
			<th>Van {icon get="bullet_arrow_up"}</th>
			<th>Tot</th>
			<th>Percentage</th>
			<th>Punten</th>
			<th title="Definitief verwijderen" class="text-center">{icon get="cross"}</th>
		</tr>
	</thead>
	<tbody>
{foreach from=$vrijstellingen item=vrijstelling}
	{include file='maalcie/vrijstelling/beheer_vrijstelling_lijst.tpl' vrijstelling=$vrijstelling}
{/foreach}
	</tbody>
</table>
