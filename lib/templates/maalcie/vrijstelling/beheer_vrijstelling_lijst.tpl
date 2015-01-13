{*
	beheer_vrijstelling_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<tr id="vrijstelling-row-{$vrijstelling->getUid()}">
	<td>
		<a href="{$smarty.const.maalcieUrl}/bewerk/{$vrijstelling->getUid()}" title="Vrijstelling wijzigen" class="btn post popup">{icon get="pencil"}</a>
	</td>
	<td>{ProfielModel::getLink($vrijstelling->getUid(), Instellingen::get('corvee', 'weergave_ledennamen_beheer'))}</td>
	<td>{$vrijstelling->getBeginDatum()|date_format:"%e %b %Y"}</td>
	<td>{$vrijstelling->getEindDatum()|date_format:"%e %b %Y"}</td>
	<td>{$vrijstelling->getPercentage()}%</td>
	<td>{$vrijstelling->getPunten()}</td>
	<td class="col-del">
		<a href="{$smarty.const.maalcieUrl}/verwijder/{$vrijstelling->getUid()}" title="Vrijstelling definitief verwijderen" class="btn post confirm">{icon get="cross"}</a>
	</td>
</tr>