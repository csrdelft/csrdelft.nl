<tr>
	<td>{$groeplid->lid_id|csrnaam:'civitas':'visitekaartje'}</td>
	{if $groep->magBeheren() OR ($groep->magAanmelden() AND LoginLid::instance()->getUid() === $groeplid->lid_id)}
		<td id="bewerk_{$groep->id}_{$groeplid->lid_id}" class="inline_edit">
			<span class="text">
				{foreach from=$groeplid->opmerking item=glfunctie name=glfunctie}
					{if $smarty.foreach.glfunctie.iteration > 1} - {/if}{$glfunctie|escape:'html'}
				{/foreach}
			</span>
			{if $groep instanceof Ketzer}
				{foreach from=$groep->getKetzerSelectors() item=select}
					<select name="opmerking" class="editbox" id="functie_input_{$groep->id}{$groeplid->lid_id}">
						{foreach from=$select->getKetzerOpties() item=optie}
							<option value="{$optie->id}"{if $optie->waarde === $groeplid->opmerking} selected="selected"{/if}>{$optie->waarde}</option>
						{/foreach}
					</select>
				{/foreach}
			{else}
				{strip}
					<input type="text" maxlength="25" value="
						   {foreach from=$groeplid->opmerking item=glfunctie name=glfunctie}
							   {if $smarty.foreach.glfunctie.iteration > 1} - {/if}
							   {$glfunctie|escape:'html'}
						   {/foreach}
						   " class="editbox" />
				{/strip}
			{/if}
		</td>
	{else}
		<td>
			<em>
				{foreach from=$groeplid->opmerking item=glfunctie name=glfunctie}
					{if $smarty.foreach.glfunctie.iteration > 1} - {/if}{$glfunctie|escape:'html'}
				{/foreach}
			</em>
		</td>
	{/if}
	<td>
		{if !($groep instanceof OpvolgbareGroep)}
			<a href="/groepen/lidstatus/{get_class($groep)}/{$groep->id}/{$groeplid->lid_id}/{GroepStatus::OT}" title="Maak o.t. lid" onclick="return confirm('Weet u zeker dat u deze bewoner naar de oudbewonersgroep wilt verplaatsen?')">&raquo;</a>
		{/if}
		{if $groep->magBeheren() OR ($groep->magAanmelden() AND LoginLid::instance()->getUid() === $groeplid->lid_id)}
			<a href="/groepen/afmelden/{get_class($groep)}/{$groep->id}/{$groeplid->lid_id}" title="Verwijder lid uit groep">X</a>
		{/if}
	</td>
</tr>