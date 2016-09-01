{if $album->hasFotos()}
	<script type="text/javascript">
		try {
			$(function () {
				$("#{$sliderId}").jGallery({
					"mode": "slider",
					"width": "auto",
					"height": "{$height}px",
					"slideshowInterval": "{$interval}s",
					"slideshowRandom": {if $random}true{else}false{/if},
					"autostartAtImage": {if $random}randomIntFromInterval(0, $('#{$sliderId} img').length - 1){else}0{/if},
					"hideThumbnailsOnInit": true,
					"transition": "random",
					"transitionBackward": "random",
					"transitionCols": 1,
					"transitionRows": 1,
					"backgroundColor": "FFFFFF",
					"textColor": "000000"
				});
			});
		}
		catch (err) {
			console.log(err);
			// Missing js file
		}
	</script>
	<div id="{$sliderId}" class="hidden">
		{foreach from=$album->getFotos() item=foto}
			<img src="{$foto->getResizedUrl()}" />
		{/foreach}
	</div>
{/if}