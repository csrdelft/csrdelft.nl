{if $album->hasFotos()}
	<script type="text/javascript" src="/layout/js/jquery/plugins/jgallery.js?v=1.4.1"></script>
	<script type="text/javascript">
		try {
			$(function () {
				$("#gallery").jGallery({
					"width": "750px",
					"transition": "random",
					"transitionBackward": "random",
					"transitionCols": "1",
					"transitionRows": "1",
					"thumbnailsPosition": "bottom",
					"thumbType": "image",
					"backgroundColor": "FFFFFF",
					"textColor": "000000",
					"mode": "slider"
				});
			});
		}
		catch (err) {
			// Missing js file
		}
	</script>
	<div class="bb-block" style="padding:0;">
		<div id="gallery">
			{foreach from=$album->getFotos() item=foto}
				<img src="{$foto->getResizedUrl()}" />
			{/foreach}
		</div>
	</div>
{/if}