{*
	beheer_instelling_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<tr id="instelling-row-{$instelling->getInstellingId()}">
	<td>
		<a href="{$globals.taken_module}/bewerk/{$instelling->getInstellingId()}" title="Instelling wijzigen" class="knop post confirm popup">{icon get="pencil"}</a>
	</td>
	<td><nobr>{$instelling->getInstellingId()|replace:'_':' '}</nobr></td>
	<td>{$instelling->getWaarde()}</td>
	<td class="col-del">
		<a href="{$globals.taken_module}/reset/{$instelling->getInstellingId()}" title="Instelling resetten" class="knop post confirm">{icon get="arrow_rotate_anticlockwise"}</a>
	</td>
</tr>