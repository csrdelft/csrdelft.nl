{$melding}
<ul class="horizontal">
{foreach from=$groeptypes item=groeptype}
	<li>
		{if $groeptype.id==$groepen->getId()}<strong>{/if}
			<a href="/groepen/{$groeptype.naam}/">{$groeptype.naam}</a>
		{if $groeptype.id==$groepen->getId()}</strong>{/if}
	</li>
{/foreach}
</ul>
<hr />
<div id="groepLijst">
<ul style="float: left;">
{foreach from=$groepen->getGroepen() item=groep name=g}
	<li style="list-style-type: none;"><a href="#groep{$groep->getId()}">{$groep->getSnaam()}</a></li>
	{if $smarty.foreach.g.iteration==$smarty.foreach.g.total %2 }</ul><ul style=" clear: none;"> {/if}
	{*	TODO: nog een mooie tweekolommentabel hier van maken... 
		halverwege dit ertussen gooien:</ul><ul style=" clear: none;"> *}
{/foreach}	
</ul>
</div>
{$groepen->getBeschrijving()|ubb}
<div class="clear"></div>
{foreach from=$groepen->getGroepen() item=groep}
	<div class="groep" id="groep{$groep->getId()}">
		<div class="groepleden">
			<table>
				{foreach from=$groep->getLeden() item=groeplid}
					<tr>
						<td>{$groeplid.uid|csrnaam:'civitas'}</td>
						<td><em>{$groeplid.functie|escape:'html'}</em></td>
					</tr>
				{/foreach}
			</table>
		</div>
		<h2><a href="/groepen/{$groepen->getNaam()}/{$groep->getSnaam()}/">{$groep->getNaam()}</a></h2>
		{$groep->getSbeschrijving()|ubb}
	</div>
{/foreach}
<hr class="clear" />
{if $groepen->isAdmin()}
	<a href="/groepen/{$groepen->getNaam()}/0/bewerken" class="knop">Nieuwe groep</a>
{/if}