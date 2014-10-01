{* Het Topmost block *}
<div id="mededelingen-top3block">
	{foreach from=$topmost item=mededeling}
		<div class="mededeling-grotebalk">
			<div class="titel" style="margin-left: 80px;">
				<a href="{MededelingenContent::mededelingenRoot}{$mededeling->getId()}">
					{$mededeling->getTitel()|ubb|html_substr:"90":"…"}
				</a>
			</div>
			<div class="plaatje" style="margin-top: 3px;">
				<a href="{MededelingenContent::mededelingenRoot}{$mededeling->getId()}">
					<img src="{$CSR_PICS}/nieuws/{$mededeling->getPlaatje()}" width="70px" height="70px" alt="{$mededeling->getPlaatje()|escape:'html'}" />
				</a>
			</div>
			<div class="bericht">
				{$mededeling->getTekst()|ubb|html_substr:"250":"…"}
				<small class="float-right"><a href="{MededelingenContent::mededelingenRoot}{$mededeling->getId()}">Verder lezen »</a></small>
			</div>
			<div class="clear"></div>
		</div>
	{/foreach}
</div>