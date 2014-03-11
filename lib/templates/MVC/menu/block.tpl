<div>
{if $root->link}<a href="{$root->link}">{/if}<h1>{$root->tekst}</h1>{if $root->link}</a>{/if}
{foreach from=$root->children item=item}
<div class="item{if startsWith($path, $item->link)} active{/if}">&raquo; <a href="{$item->link}" title="{$item->tekst}">{$item->tekst}</a></div>
{/foreach}
</div>