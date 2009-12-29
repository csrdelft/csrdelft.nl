{foreach from=$items item=item}
{if $item instanceof Lid}{* geen verjaardagen hier. *}{else}
{$item->getBeginMoment()|date_format:"%d-%m %H:%M"} [url=/actueel/agenda/maand/{$item->getBeginMoment()|date_format:"%Y-%m"}/]{$item->getTitel()}[/url]
{/if}
{/foreach}
