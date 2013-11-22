{*
	beheer_instellingen.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<p>
Op deze pagina kunt u instellingen wijzigen en resetten.
Onderstaande tabel toont alle instellingen in het systeem.
</p>
<p>
N.B. Deze instellingen zijn essentieel voor het systeem!
</p>
<div style="float: right;"><a href="/actueel/taken/conversie" title="Converteer data van oude systeem naar het nieuwe systeem" class="knop get confirm">{icon get="script_go"} Conversiescript</a></div>
<table id="taken-tabel" class="taken-tabel">
	<thead>
		<tr>
			<th>Wijzig</th>
			<th>Id</th>
			<th>Waarde</th>
			<th>Reset</th>
		</tr>
	</thead>
	<tbody>
{foreach from=$instellingen item=instelling}
	{include file='taken/instelling/beheer_instelling_lijst.tpl' instelling=$instelling}
{/foreach}
	</tbody>
</table>