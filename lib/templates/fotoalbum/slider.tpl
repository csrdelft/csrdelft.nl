<div style="height: {$height}px">
	<carousel
		:per-page="1"
		:autoplay="true"
		:autoplay-timeout="{$interval}"
		:autoplay-hover-pause="true"
		:pagination-enabled="false"
		:loop="true">
		{foreach from=$fotos item=foto}
			<slide><img src="{$foto['url']}" alt=""/></slide>
		{/foreach}
	</carousel>
</div>
