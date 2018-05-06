{foreach from=$items item=item}
{if $item instanceof CsrDelft\model\instance\Profiel}{* geen verjaardagen *}{else}
{$item->getBeginMoment()|date_format:"%A %d-%m %H:%M"} [url={$smarty.const.CSR_ROOT}/agenda/maand/{$item->getBeginMoment()|date_format:"%Y-%m"}/]{$item->getTitel()|bbcode:"mail"|strip_tags}[/url]
{/if}
{/foreach}