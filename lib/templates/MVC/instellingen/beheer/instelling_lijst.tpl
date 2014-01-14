{*
	instelling_lijst.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<tr id="instelling-row-{$instelling->instelling_id}">
	<td>
		<a href="/instellingenbeheer/bewerk/{$instelling->module}/{$instelling->instelling_id}" title="Instelling wijzigen" class="knop post confirm popup">{icon get="pencil"}</a>
	</td>
	<td><nobr>{$instelling->instelling_id|replace:'_':' '}</nobr></td>
	<td>{$instelling->waarde}</td>
	<td class="col-del">
		<a href="/instellingenbeheer/reset/{$instelling->module}/{$instelling->instelling_id}" title="Instelling resetten" class="knop post confirm">{icon get="arrow_rotate_anticlockwise"}</a>
	</td>
</tr>