{if $album->hasFotos()}
	<script type="text/javascript">
		try {
			$(function () {
				$("#{$sliderId}").jGallery({
					"mode": "slider",
					"width": "auto",
					"height": "{$galleryHeight}",
					"slideshowInterval": "{Instellingen::get('fotoalbum', 'slider_interval')}",
					"slideshowRandom": {Instellingen::get('fotoalbum', 'slider_random')},
					"autostartAtImage": randomIntFromInterval(0, $('#{$sliderId} img').length - 1),
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
	<div id="{$sliderId}">
		{foreach from=$album->getFotos() item=foto}
			<img src="{$foto->getResizedUrl()}" />
		{/foreach}
	</div>
{/if}