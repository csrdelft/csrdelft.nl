<div id="groep{$groep->getId()}" class="ubb_block ubb_groep">
 	<div class="groepleden">
		{if $groep->toonPasfotos()}
			{assign var='actie' value='pasfotos'}
		{/if}
		{include file='groepen/groepleden.tpl'}
	</div>
	<h2>{$groep->getLink()}</h2>
	<p>{$groep->getSbeschrijving()|ubb}</p>
	<div class="clear">&nbsp;</div>
</div>
