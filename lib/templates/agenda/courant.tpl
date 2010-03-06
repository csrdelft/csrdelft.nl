{foreach from=$items item=item}
{if $item instanceof Lid}{* 
	geen verjaardagen hier. 
*}{else}
{$item->getBeginMoment()|date_format:"%A %d-%m %H:%M"}  [url=http://csrdelft.nl/actueel/agenda/maand/{$item->getBeginMoment()|date_format:"%Y-%m"}/]{$item->getTitel()}[/url]
{/if}
{/foreach}
