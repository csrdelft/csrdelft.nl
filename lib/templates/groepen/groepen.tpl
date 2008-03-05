
<ul class="horizontal">
{foreach from=$groepen->getGroeptypes() item=groeptype}
	<li>
		{if $groeptype.id==$groepen->getId()}<strong>{/if}
			<a href="/groepen/groepen.php?gtype={$groeptype.naam}">{$groeptype.naam}</a>
		{if $groeptype.id==$groepen->getId()}</strong>{/if}
	</li>
{/foreach}
</ul>
<hr />
<div id="groepLijst">
<ul style="float: left;">
{foreach from=$groepen->getGroepen() item=groep}
	<li style="list-style-type: none;"><a href="#groep{$groep->getId()}">{$groep->getSnaam()}</a></li>
	
	{*	TODO: nog een mooie tweekolommentabel hier van maken... 
		halverwege dit ertussen gooien:</ul><ul style=" clear: none;"> *}
{/foreach}	
</ul>
</div>
{$groepen->getBeschrijving()|ubb}

{foreach from=$groepen->getGroepen() item=groep}
	<div class="groep" id="groep{$groep->getId()}">
		<div class="groepleden">
			<table>
				{foreach from=$groep->getLeden() item=groeplid}
					<tr>
						<td>{$groeplid.uid|csrnaam:'civitas'}</td>
						<td><em>{$groeplid.functie|escape:'html'}</em></td>
						{if $groep->magBewerken()}
							<td><a href="/groepen/groep/{$groep->getId()}/verwijder/lid/{$groeplid.uid}">X</a></td>
						{/if}
					</tr>
				{/foreach}
			</table>
		</div>
		<h2><a href="/groepen/{$groepen->getNaam()}/{$groep->getSnaam()}/">{$groep->getNaam()}</a></h2>
		{$groep->getSbeschrijving()|ubb}
	</div>
{/foreach}
<div class="clear"></div>