<ul class="horizontal">
{foreach from=$root->children item=item}
<li class="item{if $item->current} active{/if}">&raquo; <a href="{$item->link}" title="{$item->tekst}">{$item->tekst}</a></li>
{/foreach}
</ul>
<hr/>
<table style="width: 100%;"><tr id="melding"><td id="melding-veld">{SimpleHtml::getMelding()}</td></tr></table>
<h1>{$titel}</h1>