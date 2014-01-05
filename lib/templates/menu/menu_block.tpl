<div id="zijbalk_gasnelnaar"><h1>Ga snel naar</h1>
{foreach from=$root->children item=item}
	<div class="item"{if $item === $huidig} style="font-weight: bold;"{/if}>&raquo; <a href="{$item->getLink()}"title="{$item->getTekst()}">{$item->getTekst()}</a></div>
{/foreach}
</div>