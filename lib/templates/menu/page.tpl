<ul class="horizontal">
	{foreach from=$root->children item=item}
		{if $item->magBekijken()}
			<li class="item{if $item->active} active{/if}">&raquo; <a href="{$item->link}" title="{$item->tekst}">{$item->tekst}</a></li>
		{/if}
	{/foreach}
</ul>
<hr/>
<table><tr id="melding"><td id="melding-veld">{getMelding()}</td></tr></table>
<h1>{$titel}</h1>