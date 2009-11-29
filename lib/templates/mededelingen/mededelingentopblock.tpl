{* Het Topmost block *}
<div id="mededelingen-top3block">
{foreach from=$topmost item=mededeling}
	<div class="mededeling-grotebalk">
		<div class="plaatje">
			<a href="{$mededelingenRoot}{$mededeling->getId()}">
				<img src="{$csr_pics}nieuws/{$mededeling->getPlaatje()}" width="70px" height="70px" alt="{$mededeling->getPlaatje()|escape:'html'}" />
				</a>
		</div>
		<div class="titel">
			<a href="{$mededelingenRoot}{$mededeling->getId()}">
				{$mededeling->getAfgeknipteTitel()}
				</a>
			</div>
		<div class="bericht">{$mededeling->getAfgeknipteTekst()}</div>
	</div>
{/foreach}
</div>