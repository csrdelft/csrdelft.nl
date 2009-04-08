<div id="groep{$groep->getId()}" class="groep_ubb" style="margin: 10px; padding: 5px 10px; border: 1px solid black;">
 	<div style="float: right">
		{if $groep->toonPasfotos() AND $lid->instelling('groepen_toonPasfotos')=='ja'}
			{assign var='actie' value='pasfotos'}
		{/if}
		{include file='groepen/groepleden.tpl'}
	</div>
	<h2>{$groep->getLink()}</h2>
	<p>{$groep->getSbeschrijving()|ubb}</p>
	<div class="clear">&nbsp;</div>
</div>
