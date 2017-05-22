<div class="zijbalk-kopje"><a href="{CsrDelft\view\mededelingen\MededelingenView::mededelingenRoot}">Mededelingen{if isset($mcount) AND $mcount > 0} &nbsp;<span class="badge" title="{$mcount} mededeling(en) wachten op goedkeuring">{$mcount}</span>{/if}</a></div>
{foreach from=$mededelingen item=mededeling}
	<div class="item">
		{$mededeling->datum|date_format:"%d-%m"}
		<a href="{CsrDelft\view\mededelingen\MededelingenView::mededelingenRoot}{$mededeling->id}"
			title="[{$mededeling->titel|escape:'html'}] {$mededeling->getTekstVoorZijbalk()|escape:'html'}">{$mededeling->getTitelVoorZijbalk()|escape:'html'}</a>
	</div>
{/foreach}
