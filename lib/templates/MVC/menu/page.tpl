<ul class="horizontal">
{foreach from=$root->children item=item}
<li class="item{if startsWith($path, $item->link)} active{/if}">&raquo; <a href="{$item->link}" title="{$item->tekst}">{$item->tekst}</a></li>
{/foreach}
</ul>
<hr/>
<table style="width: 100%;"><tr id="melding"><td id="melding-veld">{$view->getMelding()}</td></tr></table>
<h1>{$view->getTitel()}</h1>