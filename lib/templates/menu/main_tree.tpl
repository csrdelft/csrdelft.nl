{foreach from=$parent->getChildren() item=item}
	{if $item->tekst == 'Personal'}
		{include file='menu/personal.tpl' parent=$item}
	{elseif $item->magBekijken()}
		{if $item->hasChildren()}
			<li class="has-children">
				<a href="#0">{$item->tekst}{if $item->tekst == 'Forum' AND isset($fcount) AND $fcount > 0}</a> &nbsp;<a href="/forum/wacht" class="badge badge-alert">{$fcount}{/if}</a>
				<ul class="is-hidden">
					<li class="go-back"><a href="#0">{$item->tekst}</a></li>
					{include file='menu/main_tree.tpl' parent=$item}
				</ul>
			</li>
		{else}
			<li><a href="{$item->link}">{$item->tekst}{if $item->tekst == 'Mededelingen' AND isset($mcount) AND $mcount > 0} &nbsp;<span class="badge badge-alert">{$mcount}</span>{/if}</a></li>
		{/if}
	{/if}
{/foreach}