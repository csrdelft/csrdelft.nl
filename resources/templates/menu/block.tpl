<div class="zijbalk-kopje{if $root->tekst == 'Sponsors'} ads{/if}">
	<a href="{if $root->link}{$root->link}{else}#0{/if}">{$root->tekst}</a>
</div>
{foreach from=$root->children item=item}
{if $item->magBekijken()}<div class="item{if $item->active} active{/if}{if $root->tekst == 'Sponsors'} ads{/if}">&raquo;
<a href="{$item->link}" title="{$item->tekst}"{if $item->isOngelezen()} class="{CsrDelft\model\LidInstellingenModel::get('forum', 'ongelezenWeergave')}"{/if}>{$item->tekst}</a></div>
{/if}
{/foreach}