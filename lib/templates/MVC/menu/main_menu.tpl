<nav id="cd-lateral-nav">
	{$menuzoekform->view()}
	<ul class="cd-navigation">
		{foreach from=$root->children item=item}
			{if $item->magBekijken()}
				<li class="{if $item->hasChildren()}item-has-children{/if} {if $item->active}active{/if}">
					<a class="{if $item->hasChildren()}toggle-group{/if}" href="{$item->link}" title="{$item->tekst}">{$item->tekst}</a>
					{if $item->hasChildren()}
						<ul class="sub-menu">
							{foreach from=$item->children item=child}
								{if $child->magBekijken()}
									<li><a href="{$child->link}" title="{$child->tekst}"{if $child->active} class="active"{/if}>{$child->tekst}</a></li>
									{/if}
									{foreach from=$child->children item=level3}
										{if $level3->magBekijken()}
										<li class="verborgen"><a href="{$level3->link}" title="{$level3->tekst}"{if $level3->active} class="active"{/if}>{$level3->tekst}</a></li>
										{/if}
									{/foreach}
								{/foreach}
						</ul>
					{/if}
				</li>
			{/if}
		{/foreach}
	</ul>
</nav>