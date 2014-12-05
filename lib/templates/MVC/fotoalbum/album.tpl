{if $album->hasFotos()}
	<script type="text/javascript" src="/layout/js/jquery/plugins/jgallery.js?v=1.4.1"></script>
	<script type="text/javascript">
		try {
			$(function () {
				$('#gallery').jGallery({
					"height": "897px",
					"mode": "standard",
					"canChangeMode": true,
					"canZoom": true,
					"zoomSize:": "original",
					"backgroundColor": "fff",
					"textColor": "193b61",
					"thumbType": "image",
					"thumbWidth": 150,
					"thumbHeight": 150,
					"thumbWidthOnFullScreen": 150,
					"thumbHeightOnFullScreen": 150,
					"thumbnailsPosition": "bottom",
					"hideThumbnailsOnInit": false,
					"canMinimalizeThumbnails": true,
					"transition": "fade_moveFromRight",
					"transitionBackward": "fade_moveFromLeft",
					"transitionCols": "1",
					"transitionRows": "1",
					"title": true,
					"titleExpanded": false,
					"tooltipSeeAllPhotos": "Grid",
					"tooltipSeeOtherAlbums": "Toon sub-albums",
					"slideshowInterval": "4s"
				});
				$('#gallery').css('max-height', 0);
				var container = $('div.jgallery');
				// foto url
				container.find('div.title').off();
				container.find('div.title').on('click', function (event) {
					selectText(this);
				});
				// zoom full resolution
				$('span.resize.jgallery-btn').on('click', function (event) {
					var zoom = container.find('div.zoom-container');
					if (zoom.attr('data-size') === 'fill') {
						$(this).removeClass('fa-search-minus').addClass('fa-search-plus');
						return;
					}
					else if (zoom.attr('data-size') !== 'original') {
						return;
					}
					var foto = zoom.find('img.active');
					var href = $('#gallery').find('a[href="' + foto.attr('src') + '"]').attr('data-href');
					if (typeof href === 'string') {
						container.find('div.overlay, div.imageLoaderPositionAbsolute').fadeIn();
						foto.attr('src', href).one('load', function () {
							foto.css({
								"width": this.naturalWidth,
								"height": this.naturalHeight,
								"margin-left": -this.naturalWidth / 2,
								"margin-top": -this.naturalHeight / 2
							});
							container.find('div.overlay, div.imageLoaderPositionAbsolute').fadeOut();
						});
					}
					else {
						foto.css({
							"max-width": "",
							"max-height": "",
							"width": foto[0].naturalWidth,
							"height": foto[0].naturalHeight,
							"margin-left": -foto[0].naturalWidth / 2,
							"margin-top": -foto[0].naturalHeight / 2
						});
					}
				});
				// preload next/prev
				var next = function (event) {
					container.find('div.overlay').css('display', 'none');
					var preload = container.find('a.active').next('a');
					if (preload.length === 1) {
						preloadImg(preload.attr('href'));
					}
				};
				var prev = function (event) {
					container.find('div.overlay').css('display', 'none');
					var preload = container.find('a.active').prev('a');
					if (preload.length === 1) {
						preloadImg(preload.attr('href'));
					}
				};
				container.find('div.right').on('click', next);
				container.find('div.left').on('click', prev);
				container.find('span.next').on('click', next);
				container.find('span.prev').on('click', prev);
				$(document).on('keydown', function (event) {
					if (event.keyCode === 39) {
						next();
					}
					else if (event.keyCode === 37) {
						prev();
					}
					else if (event.keyCode === 27 && container.hasClass('jgallery-full-screen')) {
						$('span.change-mode.jgallery-btn').click();
					}
				});
				// toggle thumbs fullscreen
				container.find('span.change-mode').on('click', function (event) {
					var btn = container.find('.minimalize-thumbnails');
					if (btn.hasClass('inactive') !== container.hasClass('jgallery-full-screen')) {
						btn.click();
					}
				});
				container.find('span.full-screen').on('click', function (event) {
					var btn = container.find('.minimalize-thumbnails');
					if (btn.hasClass('inactive')) {
						btn.click();
					}
				});
			});
			/* img class="photoTag" data-fotoalbum="$album->getSubDir()"
			 $(document).ready(function () {
			 $('.photoTag').photoTag({
			 requesTagstUrl: "/fotoalbum/gettags/" + $(this).attr('data-fotoalbum'),
			 deleteTagsUrl: "/fotoalbum/deltag/" + $(this).attr('data-fotoalbum'),
			 addTagUrl: "/fotoalbum/addtag/" + $(this).attr('data-fotoalbum'),
			 parametersForNewTag: {
			 name: {
			 parameterKey: 'name',
			 isAutocomplete: true,
			 autocompleteUrl: "/tools/naamlink.php?naam=" + $(this).val() + "&zoekin=leden",
			 label: 'Naam of lidnummer'
			 }
			 }
			 });
			 });
			 */
		}
		catch (err) {
			// Missing js file
		}
	</script>
{/if}
<div class="float-right" style="margin-top: 30px;">
	{if LoginModel::mag('P_ALBUM_ADD')}
		<a class="btn" href="/fotoalbum/uploaden/{$album->getSubDir()}">{icon get="picture_add"} Toevoegen</a>
		<a class="btn post popup" href="/fotoalbum/toevoegen/{$album->getSubDir()}">{icon get="folder_add"} Nieuw album</a>
	{/if}
	{if LoginModel::mag('P_ALBUM_MOD')}
		<a href="/fotoalbum/hernoemen/{$album->getSubDir()}" class="btn post prompt redirect" title="Fotoalbum hernoemen" data="Nieuwe naam={$album->dirname|ucfirst}">{icon get=pencil} Naam wijzigen</a>
		{if $album->isEmpty()}
			<a href="/fotoalbum/verwijderen/{$album->getSubDir()}" class="btn post confirm ReloadPage" title="Fotoalbum verwijderen">{icon get=cross}</a>
		{/if}
		<a class="btn" href="/fotoalbum/verwerken/{$album->getSubDir()}">{icon get="application_view_gallery"} Verwerken</a>
	{/if}
	{if LoginModel::mag('P_LOGGED_IN') && $album->getFotos()!==false}
		<a class="btn" href="/fotoalbum/downloaden/{$album->getSubDir()}" title="Download als TAR-bestand">{icon get="picture_save"} Download album</a>
	{/if}
</div>
<h1 class="inline">{$album->dirname|ucfirst}</h1>
{if $album->hasFotos()}
	<div id="gallery">
		<div class="album" data-jgallery-album-title="{$album->dirname|ucfirst}">
			<h2>{$album->dirname|ucfirst}</h2>
			{foreach from=$album->getFotos() item=foto}
				<a class="foto" href="{$foto->getResizedUrl()}" data-href="{$foto->getFullUrl()}">
					<img src="{$foto->getThumbUrl()}" alt="{$foto->getFullUrl()|replace:"%20":" "}" />
				</a>
			{/foreach}
		</div>
		{foreach from=$album->getSubAlbums() item=subalbum}
			{if $subalbum->hasFotos()}
				<div class="album" data-jgallery-album-title="{$subalbum->dirname|ucfirst}">
					<h2>{$album->dirname|ucfirst}</h2>
					{foreach from=$subalbum->getFotos() item=foto}
						<a class="foto" href="{$foto->getResizedUrl()}">
							<img src="{$foto->getThumbUrl()}" alt="{$foto->getFullUrl()|replace:"%20":" "}" />
						</a>
					{/foreach}
				</div>
			{/if}
		{/foreach}
	</div>
{else}
	<div class="subalbums">
		{foreach from=$album->getSubAlbums() item=subalbum}
			<div class="subalbum">
				<a href="{$subalbum->getUrl()}" title="{$subalbum->getUrl()|replace:"%20":" "}">
					<img src="{$subalbum->getCoverUrl()}" alt="{$subalbum->dirname|ucfirst}" />
					<div class="subalbumname">{$subalbum->dirname|ucfirst}</div>
				</a>
			</div>
		{/foreach}
	</div>
{/if}