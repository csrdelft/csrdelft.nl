{*
	beheer_instellingen.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<p>
Op deze pagina kunt u instellingen aanmaken, wijzigen en verwijderen. Onderstaande tabel toont alle instellingen in het systeem.
</p>
<p>
N.B. Deze instellingen zijn essentieel voor het systeem!
</p>
<div style="float: right;"><a href="{$module}/nieuw" title="Nieuwe instelling" class="knop post popup">{icon get="add"} Nieuwe instelling</a></div>
<table id="taken-tabel" class="taken-tabel">
	<thead>
		<tr>
			<th>Wijzig</th>
			<th>Id</th>
			<th>Waarde</th>
			<th title="Definitief verwijderen" style="text-align: center;">{icon get="cross"}</th>
		</tr>
	</thead>
	<tbody>
{foreach from=$instellingen item=instelling}
	{include file='taken/instelling/beheer_instelling_lijst.tpl' instelling=$instelling}
{/foreach}
	</tbody>
</table>