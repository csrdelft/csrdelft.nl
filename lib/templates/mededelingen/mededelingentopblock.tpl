{* Het Topmost block *}
<div id="mededelingen-top3block">
	{foreach from=$topmost item=mededeling}
		<div class="bb-block mededeling-grotebalk">
			<div class="titel">
				<a href="{MededelingenView::mededelingenRoot}{$mededeling->id}">
					{$mededeling->titel|bbcode|html_substr:"90":"…"}
				</a>
			</div>
			<div class="plaatje">
				<a href="{MededelingenView::mededelingenRoot}{$mededeling->id}">
					<img src="/plaetjes/nieuws/{$mededeling->plaatje}" width="70px" height="70px" alt="{$mededeling->plaatje|escape:'html'}" />
				</a>
			</div>
			<div class="bericht">
				{$mededeling->tekst|bbcode|html_substr:"250":"…"}
				<small class="float-right"><a href="{MededelingenView::mededelingenRoot}{$mededeling->id}">Verder lezen »</a></small>
			</div>
			<div class="clear"></div>
		</div>
	{/foreach}
</div>