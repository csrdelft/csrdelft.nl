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
	<ul>
	{foreach from=$groepen->getGroepen() item=groep name=g}
		<li><a href="#groep{$groep->getId()}">{$groep->getSnaam()}</a></li>
	{/foreach}	
	</ul>
</div>
{$groepen->getBeschrijving()|ubb}
<div class="clear">
	{if $groepen->isAdmin()}
		<a href="/groepen/{$groepen->getNaam()}/0/bewerken" class="knop">Nieuwe groep</a>
	{/if}
</div>

{foreach from=$groepen->getGroepen() item=groep}
	<div class="groep clear" id="groep{$groep->getId()}">
		<ul class="groepleden nobullets">
			{foreach from=$groep->getLeden() item=groeplid}
				<li>{$groeplid.uid|csrnaam:'civitas'}&nbsp;<em>{$groeplid.functie|escape:'html'}</em></li>
			{/foreach}
		</ul>
		<h2><a href="/groepen/{$groepen->getNaam()}/{$groep->getSnaam()}/">{$groep->getNaam()}</a></h2>
		{$groep->getSbeschrijving()|ubb}
	</div>
{/foreach}
<hr class="clear" />
{if $groepen->isAdmin()}
	<a href="/groepen/{$groepen->getNaam()}/0/bewerken" class="knop">Nieuwe groep</a>
{/if}