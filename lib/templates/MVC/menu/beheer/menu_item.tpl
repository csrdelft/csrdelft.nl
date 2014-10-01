{*
	menu_item.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<li id="menu-item-{$item->item_id}" parentid="{$item->parent_id}" class="menu-item">
	{$item->prioriteit}
	<a href="/menubeheer/bewerken/{$item->item_id}" class="knop post modal" title="Dit agenda-item bewerken">{icon get="bewerken"}</a>
	<a href="/menubeheer/toevoegen/{$item->item_id}" class="knop post modal" title="Sub-menu-item toevoegen">{icon get="add"}</a>
	<img src="{$CSR_PICS}/famfamfam/{if $item->zichtbaar}eye{else}shading{/if}.png" title="{if $item->zichtbaar}Menu-item is nu zichtbaar.{else}Menu-item is nu onzichtbaar.{/if}" />
	{$item->tekst}
	<span style="color: grey;">{$item->item_id}</span>
	<div class="float-right">
		{if $item->rechten_bekijken !== 'P_PUBLIC'}
			&nbsp;{icon get="group_key" title="Rechten bekijken:&#013;"|cat:$item->rechten_bekijken}&nbsp;
		{/if}
		<a href="{$item->link}">{$item->link}</a>
		<a href="/menubeheer/verwijderen/{$item->item_id}" title="Dit menu-item definitief verwijderen" class="knop post confirm ReloadPage">{icon get="cross"}</a>
	</div>
	{if $item->children}
		<ul class="menubeheer-tree">
			{foreach from=$item->children item=child}
				{include file='MVC/menu/beheer/menu_item.tpl' item=$child}
			{/foreach}
		</ul>
		<hr />
	{/if}
</li>