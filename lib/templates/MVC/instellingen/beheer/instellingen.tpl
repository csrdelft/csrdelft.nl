{*
	instellingen.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<p>
Op deze pagina kunt u instellingen wijzigen en resetten.
Onderstaande tabel toont alle instellingen in het systeem.
</p>
<p>
N.B. Deze instellingen zijn essentieel voor het systeem!
</p>
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
	{include file='MVC/instellingen/beheer/instelling_lijst.tpl'}
{/foreach}
	</tbody>
</table>