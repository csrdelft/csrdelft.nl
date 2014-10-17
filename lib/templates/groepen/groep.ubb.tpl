<div id="groep{$groep->getId()}" class="bb-block bb-groep">
 	<div class="groepleden">
		{if $groep->toonPasfotos()}
			{assign var='actie' value='pasfotos'}
		{/if}
		{include file='groepen/groepleden.tpl'}
	</div>
	<h2>{$groep->getLink()}</h2>
	<p>{$groep->getSbeschrijving()|bbcode}</p>
	<div class="clear">&nbsp;</div>
</div>
