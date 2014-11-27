<div>{strip}
	{if $root->link}
		<a href="{$root->link}">
	{/if}
	<div class="zijbalk-kopje">{if $root->tekst == LoginModel::getUid()}Favorieten{else}{$root->tekst}{/if}</div>
	{if $root->link}
		</a>
	{/if}
	{foreach from=$root->children item=item}
		{if $item->magBekijken()}
			<div class="item{if $item->active} active{/if}">&raquo; <a href="{$item->link}" title="{$item->tekst}">{$item->tekst}</a></div>
		{/if}
	{/foreach}
</div>{/strip}