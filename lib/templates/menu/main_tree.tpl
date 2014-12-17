{foreach from=$parent->getChildren() item=item}
	{if $item->magBekijken()}
		{if $item->hasChildren()}
			<li class="has-children">
				<a href="#0">{$item->tekst}</a>
				<ul class="is-hidden">
					<li class="go-back"><a href="#0">{$item->tekst}</a></li>
					{include file='menu/main_tree.tpl' parent=$item}
				</ul>
			</li>
		{else}
			<li><a href="{$item->link}">{$item->tekst}</a></li>
		{/if}
	{/if}
{/foreach}