<div class="zijbalk-kopje">
	{if $root->link}<a href="{$root->link}">{/if}
	{if $root->tekst == LoginModel::getUid()}Favorieten{else}{$root->tekst}{/if}
	{if $root->link}</a>{/if}
</div>
{foreach from=$root->children item=item}
{if $item->magBekijken()}<div class="item{if $item->active} active{/if}">&raquo; <a href="{$item->link}" title="{$item->tekst}">{$item->tekst}</a></div>{/if}
{/foreach}