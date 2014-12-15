{* Het Topmost block *}
<div id="mededelingen-top3block">
	{foreach from=$topmost item=mededeling}
		<div class="bb-block mededeling-grotebalk">
			<div class="titel">
				<a href="{MededelingenView::mededelingenRoot}{$mededeling->getId()}">
					{$mededeling->getTitel()|bbcode|html_substr:"90":"…"}
				</a>
			</div>
			<div class="plaatje">
				<a href="{MededelingenView::mededelingenRoot}{$mededeling->getId()}">
					<img src="//csrdelft.nl/plaetjes/nieuws/{$mededeling->getPlaatje()}" width="70px" height="70px" alt="{$mededeling->getPlaatje()|escape:'html'}" />
				</a>
			</div>
			<div class="bericht">
				{$mededeling->getTekst()|bbcode|html_substr:"250":"…"}
				<small class="float-right"><a href="{MededelingenView::mededelingenRoot}{$mededeling->getId()}">Verder lezen »</a></small>
			</div>
			<div class="clear"></div>
		</div>
	{/foreach}
</div>