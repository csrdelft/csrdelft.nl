<div id="groep{$groep->getId()}" class="bb-block bb-groep{if $groep->getEigenaar()==1025} bb-dies2015{/if}">
	<div class="groepleden">
		{if $groep->toonPasfotos()}
			{assign var='actie' value='pasfotos'}
		{/if}
		{include file='groepen/groepleden.tpl'}
	</div>
	<div class="titel"><h3>{$groep->getLink()}</h3></div>
	<div class="beschrijving">{$groep->getSbeschrijving()|bbcode}</div>
	<div class="clear">{if $groep->getEigenaar()==1025}<img src="/plaetjes/nieuws/m.png" width="70" height="70" alt="M">{/if}&nbsp;</div>
</div>
