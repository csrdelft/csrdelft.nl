{*
	beheer_vrijstelling_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<tr id="vrijstelling-row-{$vrijstelling->getUid()}">
	<td>
		<a href="{Instellingen::get('taken', 'url')}/bewerk/{$vrijstelling->getUid()}" title="Vrijstelling wijzigen" class="knop rounded post modal">{icon get="pencil"}</a>
	</td>
	<td>{Lid::naamLink($vrijstelling->getUid(), Instellingen::get('corvee', 'weergave_ledennamen_beheer'), Instellingen::get('corvee', 'weergave_link_ledennamen'))}</td>
	<td>{$vrijstelling->getBeginDatum()|date_format:"%e %b %Y"}</td>
	<td>{$vrijstelling->getEindDatum()|date_format:"%e %b %Y"}</td>
	<td>{$vrijstelling->getPercentage()}%</td>
	<td>{$vrijstelling->getPunten()}</td>
	<td class="col-del">
		<a href="{Instellingen::get('taken', 'url')}/verwijder/{$vrijstelling->getUid()}" title="Vrijstelling definitief verwijderen" class="knop rounded post confirm">{icon get="cross"}</a>
	</td>
</tr>