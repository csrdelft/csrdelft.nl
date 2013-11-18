{*
	beheer_vrijstellingen.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<p>
Op deze pagina kunt u vrijstellingen aanmaken, wijzigen en verwijderen. Onderstaande tabel toont alle vrijstellingen in het systeem.
</p>
<p>
N.B. Pas bij het resetten van het corveejaar worden de te behalen corveepunten per jaar ({$jaarpunten}) maal het vrijstellingspercentage toegekend.
</p>
<div style="float: right;"><a href="{$module}/nieuw" title="Nieuwe vrijstelling" class="knop post popup">{icon get="add"} Nieuwe vrijstelling</a></div>
<table id="taken-tabel" class="taken-tabel">
	<thead>
		<tr>
			<th>Wijzig</th>
			<th>Lid</th>
			<th>Van</th>
			<th>Tot</th>
			<th>Percentage</th>
			<th title="Definitief verwijderen" style="text-align: center;">{icon get="cross"}</th>
		</tr>
	</thead>
	<tbody>
{foreach from=$vrijstellingen item=vrijstelling}
	{include file='taken/vrijstelling/beheer_vrijstelling_lijst.tpl' vrijstelling=$vrijstelling}
{/foreach}
	</tbody>
</table>