{$melding}
<ul class="horizontal">
{foreach from=$groeptypes item=groeptype}
	<li>
		{if $groeptype.id==$groepen->getId()}<strong>{/if}
			<a href="/actueel/groepen/{$groeptype.naam}/">{$groeptype.naam}</a>
		{if $groeptype.id==$groepen->getId()}</strong>{/if}
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
	{if $groepen->isAdmin()}
		<a href="/actueel/groepen/{$groepen->getNaam()}/0/bewerken" class="knop">Nieuwe groep</a>
	{/if}
</div>

{foreach from=$groepen->getGroepen() item=groep}
	<div class="groep clear" id="groep{$groep->getId()}">
		{if $groep->toonPasfotos() AND $loginlid->getLid()->instelling('groepen_toonPasfotos')=='ja'}
			<div class="pasfotomatrix" style="float: right;">
				{foreach from=$groep->getLeden() item=groeplid}
					{$groeplid.uid|pasfoto}
				{/foreach}
			</div>
		{else}
			<ul class="groepledenlijst nobullets">
				{foreach from=$groep->getLeden() item=groeplid}
					<li>{$groeplid.uid|csrnaam:'civitas'}{if $groep->toonFuncties()}&nbsp;<em>{$groeplid.functie|escape:'html'}{/if}</em></li>
				{/foreach}
				{if $groep->isAanmeldbaar() AND $groep->isVol()}
					<li><br />Deze groep is vol, u kunt zich niet meer aanmelden.</li>
				{/if}
			</ul>
		{/if}
		<h2><a href="/actueel/groepen/{$groepen->getNaam()}/{$groep->getId()}/">{$groep->getNaam()}</a></h2>
		{$groep->getSbeschrijving()|ubb}
	</div>
{/foreach}
<hr class="clear" />
{if $groepen->isAdmin()}
	<a href="/actueel/groepen/{$groepen->getNaam()}/0/bewerken" class="knop">Nieuwe groep</a>
{/if}

