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
					"autostartAtImage": {if $random}window.util.randomIntFromInterval(0, $('#{$sliderId} img').length - 1){else}0{/if},
					"hideThumbnailsOnInit": true,
					"transition": "random",
					"transitionBackward": "random",
					"transitionCols": 1,
					"transitionRows": 1,
					"backgroundColor": "FFFFFF",
					"textColor": "000000",
					"items": {$itemsJson}
				});
			});
		}
		catch (err) {
			console.log(err);
			// Missing js file
		}
	</script>
	<div id="{$sliderId}">
	</div>
{/if}