<div id="groep{$groep->getId()}" class="bb-block bb-groep">
 	<div class="groepleden">
		{if $groep->toonPasfotos()}
			{assign var='actie' value='pasfotos'}
		{/if}
		{include file='groepen/groepleden.tpl'}
	</div>
	<div>{$groep->getLink()}</div>
	<p>{$groep->getSbeschrijving()|bbcode}</p>
	<div class="clear">&nbsp;</div>
</div>
