<h1><a href="{$mededelingenRoot}">Mededelingen</a></h1>
{foreach from=$mededelingen item=mededeling}
	<div class="item">
		<span class="tijd">{$mededeling->getDatum()|date_format:$datumFormaat}</span>
		<a href="{$mededelingenRoot}{$mededeling->getId()}"
			title="[{$mededeling->getTitel()|escape:'html'}] {$mededeling->getTekstVoorZijbalk()|escape:'html'}">{$mededeling->getTitelVoorZijbalk()|escape:'html'}</a>
	</div>
{/foreach}