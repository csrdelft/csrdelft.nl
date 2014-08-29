{* Het Topmost block *}
<div id="mededelingen-top3block">
	{foreach from=$topmost item=mededeling}
		<div class="mededeling-grotebalk">
			<div class="plaatje">
				<a href="{MededelingenContent::mededelingenRoot}{$mededeling->getId()}">
					<img src="{$CSR_PICS}/nieuws/{$mededeling->getPlaatje()}" width="70px" height="70px" alt="{$mededeling->getPlaatje()|escape:'html'}" />
				</a>
			</div>
			<div class="titel">
				<a href="{MededelingenContent::mededelingenRoot}{$mededeling->getId()}">
					{$mededeling->getTitel()|ubb|html_substr:"40":"…"}
				</a>
			</div>
			<div class="bericht">
				{$mededeling->getTekst()|ubb|html_substr:"175":"…"}
				<small><a href="{MededelingenContent::mededelingenRoot}{$mededeling->getId()}">verder lezen</a></small>
			</div>
			<div class="clear"></div>
		</div>
	{/foreach}
</div>