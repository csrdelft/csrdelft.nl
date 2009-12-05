<h1><a href="/actueel/agenda/" title="Agenda">Agenda</a></h1>

{foreach from=$items item=item}
	{if $item instanceof Lid}
		{* geen verjaardagen hier. *}
	{else}
		<div class="item">
		{$item->getBeginMoment()|date_format:"%d-%m"} 
		<a href="/actueel/agenda/maand/{$item->getBeginMoment()|date_format:"%Y-%m"}/" title="{$item->getTitel()|htmlspecialchars}">
			{$item->getTitel()|truncate:23:"…":true}
		</a>

	</div>
	{/if}
{/foreach}
