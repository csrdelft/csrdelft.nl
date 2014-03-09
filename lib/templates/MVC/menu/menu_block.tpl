<div id="zijbalk_gasnelnaar"><h1>Ga snel naar</h1>
{foreach from=$root->children item=item}
	<div class="item"{if startsWith($path, $item->link)} style="font-weight: bold;"{/if}>&raquo; <a href="{$item->link}" title="{$item->tekst}">{$item->tekst}</a></div>
{/foreach}
</div>