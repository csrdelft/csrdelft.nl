<table>
	{foreach from=$groep->getLeden() item=groeplid}
		<tr>
			<td>{$groeplid.uid|csrnaam:'civitas'}</td>
			{if $groep->toonFuncties()}<td><em>{$groeplid.functie|escape:'html'}</em></td>{/if}
			{if $groep->magBewerken()}
				<td>
				{if $groep->getTypeId()==2 AND $groep->getStatus()=='ht'}
					<a href="/actueel/groepen/{$gtype}/{$groep->getId()}/maakLidOt/{$groeplid.uid}" title="Verplaats lid naar o.t.-groep" 
						{if !$groep->isAdmin()}onclick="return confirm('Weet u zeker dat u deze bewoner naar de oudbewonersgroep wilt verplaatsen?')"{/if}>
						&raquo;
					</a>
				{else}
					<a href="/actueel/groepen/{$gtype}/{$groep->getId()}/verwijderLid/{$groeplid.uid}" title="Verwijder lid uit groep">X</a>
				{/if}
				</td>					
			{/if}
		</tr>
	{/foreach}
</table>
