{*
	menu_item.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<li id="menu-item-{$item->item_id}" parentid="{$item->parent_id}" class="menu-item">
	{if $item->children}
	<span onclick="$(this).parent().children('ul').slideToggle();$(this).children('span.ico').toggleClass('bullet_toggle_minus bullet_toggle_plus');" style="cursor:pointer;">
		{icon get='bullet_toggle_minus'}
	</span>
	{/if}
	<span class="lichtgrijs">{$item->volgorde}</span>
	<a href="/menubeheer/bewerken/{$item->item_id}" class="btn post popup" title="Dit menu-item bewerken">{icon get="bewerken"}</a>
	{toegang P_ADMIN}
		<a href="/menubeheer/toevoegen/{$item->item_id}" class="btn post popup" title="Sub-menu-item toevoegen">{icon get="add"}</a>
	{/toegang}
	<a href="/menubeheer/zichtbaar/{$item->item_id}" class="btn post ReloadPage" title="Menu-item is nu {if !$item->zichtbaar}on{/if}zichtbaar">{if $item->zichtbaar}{icon get="eye"}{else}{icon get="shading"}{/if}</a>
	<span>{$item->tekst}</span>
	{toegang P_ADMIN}
		<span class="lichtgrijs">{$item->item_id}</span>
	{/toegang}
	<div class="float-right">
		{if $item->rechten_bekijken !== P_PUBLIC and $item->rechten_bekijken != CsrDelft\model\security\LoginModel::getUid()}
			&nbsp;{icon get="group_key" title="Rechten bekijken:&#013;"|cat:$item->rechten_bekijken}&nbsp;
		{/if}
		<a href="{$item->link}">{$item->link}</a>
		<a href="/menubeheer/verwijderen/{$item->item_id}" class="btn post confirm ReloadPage" title="Dit menu-item definitief verwijderen">{icon get="cross"}</a>
	</div>
	{if $item->children}
		<ul class="menubeheer-tree">
			{foreach from=$item->children item=child}
				{include file='menu/beheer/menu_item.tpl' item=$child}
			{/foreach}
		</ul>
		<hr />
	{/if}
</li>
