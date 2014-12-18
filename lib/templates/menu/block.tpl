<div class="zijbalk-kopje">
	<a href="{if $root->link}{$root->link}{else}#0{/if}">{$root->tekst}</a>
</div>
{foreach from=$root->children item=item}
{if $item->magBekijken()}<div class="item{if $item->active} active{/if}">&raquo; <a href="{$item->link}" title="{$item->tekst}">{$item->tekst}</a></div>{/if}
{/foreach}