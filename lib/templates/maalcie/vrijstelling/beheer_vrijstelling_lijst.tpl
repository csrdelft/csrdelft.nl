{*
	beheer_vrijstelling_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<tr id="vrijstelling-row-{$vrijstelling->uid}">
	<td>
		<a href="{$smarty.const.maalcieUrl}/bewerk/{$vrijstelling->uid}" title="Vrijstelling wijzigen" class="btn post popup">{icon get="pencil"}</a>
	</td>
	<td>{CsrDelft\model\ProfielModel::getLink($vrijstelling->uid,instelling('corvee', 'weergave_ledennamen_beheer'))}</td>
	<td>{$vrijstelling->begin_datum|date_format:"%e %b %Y"}</td>
	<td>{$vrijstelling->eind_datum|date_format:"%e %b %Y"}</td>
	<td>{$vrijstelling->percentage}%</td>
	<td>{$vrijstelling->getPunten()}</td>
	<td class="col-del">
		<a href="{$smarty.const.maalcieUrl}/verwijder/{$vrijstelling->uid}" title="Vrijstelling definitief verwijderen" class="btn post confirm">{icon get="cross"}</a>
	</td>
</tr>
