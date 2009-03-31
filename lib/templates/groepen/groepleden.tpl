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
	{if $groep->isAanmeldbaar() AND !$groep->isLid()}
		<tr class="aanmelden">
			<td colspan="3">
			{if $groep->magAanmelden()}
				{if $groep->getToonFuncties()=='niet'}
					<a href="/actueel/groepen/{$gtype}/{$groep->getId()}/aanmelden" onclick="return confirm('Weet u zeker dat u zich wilt aanmelden?')">
						Aanmelden
					</a>
				{else}
					<form action="/actueel/groepen/{$gtype}/{$groep->getId()}/aanmelden" method="post" id="aanmeldForm">
						<strong>Aanmelden</strong> (functie/opmerking mogelijk)<br />
						<input type="text" name="functie" class="functie" />&nbsp;<input type="submit" value="aanmelden" />
					</form>
				{/if}
			{elseif $groep->isVol()}
				Deze groep is vol, u kunt zich niet meer aanmelden.
			{/if}
			</td>
		</tr>
	{/if}	
</table>
