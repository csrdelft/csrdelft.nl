<div class="zijbalk-kopje"><a href="/mededelingen/">Mededelingen{if isset($mcount) AND $mcount > 0} &nbsp;<span
		class="badge" title="{$mcount} mededeling(en) wachten op goedkeuring">{$mcount}</span>{/if}</a></div>
{foreach from=$mededelingen item=mededeling}
	<div class="item">
		<a href="/mededelingen/{$mededeling->id}"
			 title="[{$mededeling->titel|escape:'html'}] {$mededeling->getTekstVoorZijbalk()|escape:'html'}">
			<span class="zijbalk-moment">{$mededeling->datum|zijbalk_date_format}</span>
			{$mededeling->getTitelVoorZijbalk()|escape:'html'}
		</a>
	</div>
{/foreach}
