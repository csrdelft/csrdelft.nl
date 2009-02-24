<div id="groep{$groep->getId()}" class="groep_ubb" style="margin: 10px; padding: 5px 10px; border: 1px solid black;">
 	{if is_array($groep->getLeden())}
 	<table style="float: right">
 		{foreach from=$groep->getLeden() item=groeplid}
			<tr>
				<td>{$groeplid.uid|csrnaam:'civitas'}</td>
				{if $groep->toonFuncties()}
					<td><em>{$groeplid.functie|escape:'htmlall'}</em></td>
				{/if}
			</tr>
		{/foreach}
	</table>
	{/if}
	<h2>{$groep->getLink()}</h2>
	<p>{$groep->getSbeschrijving()|ubb}</p><br />
	{if $groep->isAanmeldbaar() AND $groep->magAanmelden()}
		<form action="/actueel/groepen/{$groep->getType()}/{$groep->getId()}/aanmelden/return" method="post" id="aanmeldForm">
			U kunt zich hier aanmelden voor deze groep.
			{if $groep->getToonFuncties()!='niet'}
				Geef ook een opmerking/functie op:
				<br /><input type="text" name="functie" />
			{else}
				<br />
			{/if}
			<input type="submit" value="aanmelden" />
		</form>
	{/if}
	<div class="clear">&nbsp;</div>
</div>