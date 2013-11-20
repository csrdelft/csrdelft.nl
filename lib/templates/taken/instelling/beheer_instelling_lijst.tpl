{*
	beheer_instelling_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<tr id="instelling-row-{$instelling->getInstellingId()}">
	<td>
		<a href="{$module}/bewerk/{$instelling->getInstellingId()}" title="Instelling wijzigen" class="knop post confirm popup">{icon get="pencil"}</a>
	</td>
	<td>{$instelling->getInstellingId()|replace:'_':' '}</td>
	<td>{$instelling->getWaarde()}</td>
	<td style="text-align:center;">
		<a href="{$module}/reset/{$instelling->getInstellingId()}" title="Instelling resetten" class="knop post confirm">{icon get="table_refresh"}</a>
	</td>
</tr>