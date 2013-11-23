{*
	beheer_vrijstelling_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<tr id="vrijstelling-row-{$vrijstelling->getLidId()}">
	<td>
		<a href="{$module}/bewerk" post="voor_lid={$vrijstelling->getLidId()}" title="Vrijstelling wijzigen" class="knop post popup">{icon get="pencil"}</a>
	</td>
	<td>{$vrijstelling->getLid()->getNaamLink($ledenweergave, 'link')}</td>
	<td>{$vrijstelling->getBeginDatum()|date_format:"%e %b %Y"}</td>
	<td>{$vrijstelling->getEindDatum()|date_format:"%e %b %Y"}</td>
	<td>{$vrijstelling->getPercentage()}%</td>
	<td>{$vrijstelling->getPunten()}</td>
	<td class="col-del">
		<a href="{$module}/verwijder" post="voor_lid={$vrijstelling->getLidId()}" title="Vrijstelling definitief verwijderen" class="knop post confirm">{icon get="cross"}</a>
	</td>
</tr>