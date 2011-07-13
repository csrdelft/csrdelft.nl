{$melding}
<ul class="horizontal">
{foreach from=$groeptypes item=groeptype}
	<li{if $groeptype.id==$groepen->getId()} class="active"{/if}>
			<a href="/actueel/groepen/{$groeptype.naam}/">{$groeptype.naam}</a>
	</li>
{/foreach}
</ul>
<hr />
{if !$groepen->getToonHistorie()}
	<div id="groepLijst">
		<ul>
		{foreach from=$groepen->getGroepen() item=groep name=g}
			<li><a href="#groep{$groep->getId()}">{$groep->getSnaam()}</a></li>
		{/foreach}	
		</ul>
	</div>
{/if}
{$groepen->getBeschrijving()|ubb}
<div class="clear">
	{if $groepen->isAdmin() OR $groepen->isGroepAanmaker()}
		<a href="/actueel/groepen/{$groepen->getNaam()}/0/bewerken" class="knop">Nieuwe groep</a>
	{/if}	
	{if $groepen->isAdmin()}
		<a href="/actueel/groepen/{$groepen->getNaam()}/?maakOt=true" class="knop" 
			onclick="return confirm('Weet u zeker dat alle h.t. groepen in deze categorie o.t. moeten worden?')">
			Maak h.t. groepen o.t.
		</a>
	{/if}
	
</div>

{foreach from=$groepen->getGroepen() item=groep}
	<div class="groep clear" id="groep{$groep->getId()}">
		<div class="groepleden">
			{if $groep->toonPasfotos()}
				{assign var='actie' value='pasfotos'}
			{/if}
			{include file='groepen/groepleden.tpl'}
		</div>
		<h2><a href="/actueel/groepen/{$groepen->getNaam()}/{$groep->getId()}/">{$groep->getNaam()}</a></h2>
		{if $groep->getType()->getId()==11 }Ouderejaars: {$groep->getEigenaar()|perm2string}<br />{/if} {* alleen bij Sjaarsacties *}
		{$groep->getSbeschrijving()|ubb}
	</div>
{/foreach}
<hr class="clear" />
{if $groepen->isAdmin() OR $groepen->isGroepAanmaker()}
	<a href="/actueel/groepen/{$groepen->getNaam()}/0/bewerken" class="knop">Nieuwe groep</a>
{/if}

