{if $album->hasFotos()}
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
					"transition": "moveToLeft_scaleUp",
					"transitionBackward": "moveToRight_scaleUp",
					"transitionCols": "1",
					"transitionRows": "1",
					"title": true,
					"titleExpanded": false,
					"tooltipSeeAllPhotos": "Grid",
					"tooltipSeeOtherAlbums": "Toon sub-albums",
					"slideshowInterval": "3s"
				});
				$('#gallery').css('max-height', 0);
				var tagMode = false;
				var container = $('div.jgallery');
				// foto url
				container.find('div.title').off();
				container.find('div.title').on('click', function (event) {
					selectText(this);
				});
				// zoom full resolution
				var showHiRes = function () {
					var zoom = container.find('div.zoom-container');
					var foto = zoom.find('img.active');
					var href = $('#gallery').find('a[href="' + foto.attr('src') + '"]').attr('data-href');
					if (typeof href === 'string' && foto.attr('src') !== href) {
						var timer = setTimeout(function () {
							if (zoom.attr('data-size') === 'original') {
								container.find('div.overlay, div.imageLoaderPositionAbsolute').fadeIn();
							}
						}, 400);
						foto.attr('src', href).one('load', function () {
							clearTimeout(timer);
							if (zoom.attr('data-size') === 'original') {
								foto.css({
									"width": this.naturalWidth,
									"height": this.naturalHeight,
									"margin-left": -this.naturalWidth / 2,
									"margin-top": -this.naturalHeight / 2
								});
								foto.attr('data-width', this.naturalWidth);
								foto.attr('data-height', this.naturalHeight);
								$(window).resize();
							}
							container.find('div.overlay, div.imageLoaderPositionAbsolute').fadeOut();
						});
					}
					else if (zoom.attr('data-size') === 'original') {
						var foto = zoom.find('img.active');
						foto.css({
							"max-width": "",
							"max-height": "",
							"width": foto[0].naturalWidth,
							"height": foto[0].naturalHeight,
							"margin-left": -foto[0].naturalWidth / 2,
							"margin-top": -foto[0].naturalHeight / 2
						});
						foto.attr('data-width', foto[0].naturalWidth);
						foto.attr('data-height', foto[0].naturalHeight);
						$(window).resize();
					}
				};
				$('span.resize.jgallery-btn').on('click', function () {
					var zoom = container.find('div.zoom-container');
					if (zoom.attr('data-size') === 'fill') {
						$(this).removeClass('fa-search-minus').addClass('fa-search-plus');
					}
					if (zoom.attr('data-size') !== 'fit') {
						showHiRes();
					}
				});
				// BEGIN tagging code
				var getFotoTopLeft = function () {
					var img = container.find('img.active');
					return {
						x: (img.parent().width() - img.width()) / 2,
						y: (img.parent().height() - img.height()) / 2
					};
				};
				var drawTag = function (tag) {
					var img = container.find('img.active');
					// TODO
					alert(tag);
					console.log(tag);
				};
				var drawTags = function (array) {
					$.each(array, drawTag);
				};
				var showTags = function () {
					var url = container.find('div.nav-bottom div.title').html().replace('{$smarty.const.CSR_ROOT}/plaetjes', '');
					$.post('/fotoalbum/gettags' + dirname(url), {
						foto: basename(url)
					}, drawTags);
				};
				var tagFormDiv = false;
				var drawTagForm = function (html, relX, relY, size) {
					var img = container.find('img.active');
					var offset = getFotoTopLeft();
					tagFormDiv = $(html).appendTo(img.parent());
					tagFormDiv.css({
						position: 'absolute',
						top: offset.y + relY * img.height() / 100,
						left: offset.x + relX * img.width() / 100,
						'z-index': 10000
					});
					// set attr for move/resize
					tagFormDiv.attr('data-relY', relY);
					tagFormDiv.attr('data-relX', relX);
					tagFormDiv.attr('data-size', size);
					// set submit handler
					tagFormDiv.find('form').bind('ajax:complete', function (event, jqXHR, ajaxOptions) {
						console.log(event);
						console.log(jqXHR);
						console.log(ajaxOptions);
						alert();
					});
					// set focus
					tagFormDiv.find('input').focus();
				};
				var moveTagForm = function () {
					if (!tagFormDiv) {
						return;
					}
					var img = container.find('img.active');
					var offset = getFotoTopLeft();
					tagFormDiv.css({
						top: offset.y + tagFormDiv.attr('data-relY') * img.height() / 100,
						left: offset.x + tagFormDiv.attr('data-relX') * img.width() / 100
					});
				};
				var resizeTag = function () {
					//TODO
				};
				var exitTagForm = function () {
					tagFormDiv.remove();
					tagFormDiv = false;
				};
				var onAddTag = function (e) {
					if (tagFormDiv) {
						exitTagForm();
					}
					var img = container.find('img.active');
					var offset = $(this).offset();
					var relX = (e.pageX - offset.left) * 100 / img.width();
					var relY = (e.pageY - offset.top) * 100 / img.height();
					var size = 50; // TODO: dynamic
					var url = container.find('div.nav-bottom div.title').html().replace('{$smarty.const.CSR_ROOT}/plaetjes', '');
					$.post('/fotoalbum/addtag' + dirname(url), {
						foto: basename(url),
						x: Math.round(relX),
						y: Math.round(relY),
						size: Math.round(size)
					}, function (data) {
						if (typeof response === 'object') { // JSON
							drawTag(data);
						}
						else { // HTML
							drawTagForm(data, relX, relY, size);
						}
					});
				};
				var duringTagMode = function () {
					// disable nav area on img
					container.find('div.right').hide();
					container.find('div.left').hide();
					// click handler
					var img = container.find('img.active');
					img.css('cursor', 'crosshair');
					img.on('click', onAddTag);
				};
				$(window).resize(function () {
					if (tagMode) {
						duringTagMode();
						if (tagFormDiv) {
							moveTagForm();
						}
					}
					else {
						if (tagFormDiv) {
							exitTagForm();
						}
					}
				});
				// END tagging code
				// preload next/prev
				var onNextPrev = function (anchor) {
					container.find('div.overlay').css('display', 'none'); // hide loading div
					if (tagMode) {
						duringTagMode();
					}
					if (anchor.length === 1) {
						preloadImg(anchor.attr('href')); // preload image from url param
					}
					var zoom = container.find('div.zoom-container');
					if (zoom.attr('data-size') !== 'fit') { // if zoomed in
						setTimeout(showHiRes, 1); // show hi-res via background process
						if (zoom.attr('data-size') === 'fill') { // workaround zoom mode display icon for hi-res img replacement hack
							$('span.resize.jgallery-btn').removeClass('fa-search-minus').addClass('fa-search-plus'); // change zoom button icon
						}
						if (anchor.length === 1) {
							var href = $('#gallery').find('a[href="' + anchor.attr('href') + '"]').attr('data-href'); // replace full-res href in data attr
							if (typeof href === 'string') {
								preloadImg(href); // preload hi-res image
							}
						}
					}
				};
				var next = function () {
					onNextPrev(container.find('a.active').next('a'));
				};
				var prev = function () {
					onNextPrev(container.find('a.active').prev('a'));
				};
				container.find('div.right').on('click', next);
				container.find('div.left').on('click', prev);
				container.find('span.next').on('click', next);
				container.find('span.prev').on('click', prev);
				$(window).on('keydown', function (event) {
					if (event.keyCode === 39) { // arrow right
						next();
					}
					else if (event.keyCode === 37) { // arrow left
						prev();
					}
					else if (event.keyCode === 27) { // esc
						event.preventDefault();
						if (tagMode) {
							if (tagFormDiv) {
								exitTagForm();
							}
						}
						else {
							$('span.change-mode').click();
						}
					}
					else if (event.keyCode === 122) { // f11
						event.preventDefault();
						container.find('span.change-mode').click();
					}
				});
				var fnToggleFullscreen = function () {
					if (container.hasClass('jgallery-full-screen')) {
						window.scrollTo(0, 0);
						window.clearTimeout($('#cd-main-trigger').data('timer'));
						setTimeout(function () {
							$('#cd-main-trigger').addClass('fade');
							$('#cd-user-avatar').addClass('fade');
						}, 1000);
						var docelem = $('body').get(0);
						if (docelem.requestFullscreen) {
							docelem.requestFullscreen();
						} else if (docelem.webkitRequestFullscreen) {
							docelem.webkitRequestFullscreen();
						} else if (docelem.mozRequestFullScreen) {
							docelem.mozRequestFullScreen();
						} else if (docelem.msRequestFullscreen) {
							docelem.msRequestFullscreen();
						}
					}
					else {
						$('#cd-main-trigger').removeClass('fade');
						$('#cd-user-avatar').removeClass('fade');
						if (document.exitFullscreen) {
							document.exitFullscreen();
						}
						else if (document.webkitExitFullscreen) {
							document.webkitExitFullscreen();
						}
						else if (document.mozCancelFullScreen) {
							document.mozCancelFullScreen();
						}
						else if (document.msExitFullscreen) {
							document.msExitFullscreen();
						}
					}
				};
				// toggle thumbs fullscreen
				var btn = container.find('.minimalize-thumbnails');
				container.find('span.change-mode').on('click', function (event) {
					if (btn.hasClass('inactive') !== container.hasClass('jgallery-full-screen')) {
						btn.click();
					}
					fnToggleFullscreen();
				});
				container.find('span.full-screen').on('click', function (event) {
					if (btn.hasClass('inactive')) {
						btn.click();
					}
				});
				// fullscreen GET param
				if (window.location.href.indexOf('?fullscreen') > 0 && !container.hasClass('jgallery-full-screen')) {
					$('span.change-mode').click();
				}
				// knopje subalbums
				container.find('.fa-list-ul').removeClass('fa-list-ul').addClass('fa-folder-open-o');
				// knopje map omhoog
				$('<span class="fa fa-arrow-circle-up jgallery-btn jgallery-btn-small" tooltip="Open parent album"></span>').click(function () {
					var url = container.find('div.nav-bottom div.title').html().replace('{$smarty.const.CSR_ROOT}/plaetjes', '');
					var fullscreen = '';
					if (container.hasClass('jgallery-full-screen')) {
						fullscreen = '?fullscreen';
					}
					window.location.href = dirname(dirname(url)).replace('plaetjes/', '') + fullscreen;
				}).prependTo(container.find('div.icons'));
		{if LoginModel::mag('P_ALBUM_MOD') OR $album->isOwner()}
				// knopje verwijderen
				$('<span class="fa fa-times jgallery-btn jgallery-btn-small" tooltip="Foto verwijderen"></span>').click(function () {
					if (!confirm('Foto definitief verwijderen. Weet u het zeker?')) {
						return false;
					}
					var url = container.find('div.nav-bottom div.title').html().replace('{$smarty.const.CSR_ROOT}/plaetjes', '');
					$.post('/fotoalbum/verwijderen' + dirname(url), {
						foto: basename(url)
					}, page_reload);
				}).insertAfter(btn);
				// knopje albumcover
				$('<span class="fa fa-folder jgallery-btn jgallery-btn-small" tooltip="Foto instellen als albumcover"></span>').click(function () {
					var url = container.find('div.nav-bottom div.title').html().replace('{$smarty.const.CSR_ROOT}/plaetjes', '');
					$.post('/fotoalbum/albumcover' + dirname(url), {
						foto: basename(url)
					}, page_redirect);
				}).insertAfter(btn);
				// knopje linksom draaien
				$('<span class="fa fa-undo jgallery-btn jgallery-btn-small" tooltip="Foto tegen de klok in draaien"></span>').click(function () {
					var url = container.find('div.nav-bottom div.title').html().replace('{$smarty.const.CSR_ROOT}/plaetjes', '');
					$.post('/fotoalbum/roteren' + dirname(url), {
						foto: basename(url),
						rotation: -90
					}, page_reload);
				}).insertAfter(btn);
				// knopje rechtsom draaien
				$('<span class="fa fa-repeat jgallery-btn jgallery-btn-small" tooltip="Foto met de klok mee draaien"></span>').click(function () {
					var url = container.find('div.nav-bottom div.title').html().replace('{$smarty.const.CSR_ROOT}/plaetjes', '');
					$.post('/fotoalbum/roteren' + dirname(url), {
						foto: basename(url),
						rotation: 90
					}, page_reload);
				}).insertAfter(btn);
		{/if}
		{if LoginModel::mag('P_LOGGED_IN')}
				// knopje downloaden
				$('<span class="fa fa-download jgallery-btn jgallery-btn-small" tooltip="Foto in origineel formaat downloaden"></span>').click(function () {
					var url = container.find('div.nav-bottom div.title').html().replace('{$smarty.const.CSR_ROOT}/plaetjes', '');
					window.location.href = '/fotoalbum/download' + url;
				}).insertAfter(btn);
				// knopje taggen
				$('<span class="fa fa-tags jgallery-btn jgallery-btn-small" tooltip="Leden etiketteren"></span>').click(function () {
					if (tagMode) {
						tagMode = false;
						// enable nav area on img
						container.find('div.right').show();
						container.find('div.left').show();
					}
					else {
						tagMode = true;
						duringTagMode();
					}
				}).insertAfter(btn);
		{/if}
			});
			/* img class="photoTag" data-fotoalbum="$album->subdir"
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
			console.log(err);
			// Missing js file
		}
	</script>
{/if}
<div class="float-right" style="margin-top: 30px;">
	{if LoginModel::mag('P_ALBUM_ADD')}
		<a class="btn" href="/fotoalbum/uploaden/{$album->subdir}">{icon get="picture_add"} Toevoegen</a>
		<a class="btn post popup" href="/fotoalbum/toevoegen/{$album->subdir}">{icon get="folder_add"} Nieuw album</a>
	{/if}
	{if LoginModel::mag('P_ALBUM_MOD') OR $album->isOwner()}
		<a href="/fotoalbum/hernoemen/{$album->subdir}" class="btn post prompt redirect" title="Fotoalbum hernoemen" data="Nieuwe naam={$album->dirname|ucfirst}">{icon get=pencil} Naam wijzigen</a>
		{if $album->isEmpty()}
			<a href="/fotoalbum/verwijderen/{$album->subdir}" class="btn post confirm redirect" title="Fotoalbum verwijderen">{icon get=cross} Verwijderen</a>
		{/if}
		{if LoginModel::mag('P_ALBUM_MOD')}
			<a class="btn popup confirm" href="/fotoalbum/verwerken/{$album->subdir}" title="Fotoalbum verwerken (dit kan even duren). Verwijder magick-* files in /tmp handmatig bij timeout!">{icon get="application_view_gallery"} Verwerken</a>
		{/if}
	{/if}
	{if LoginModel::mag('P_LOGGED_IN') AND $album->hasFotos()}
		<a class="btn" href="/fotoalbum/downloaden/{$album->subdir}" title="Download als TAR-bestand">{icon get="picture_save"} Download album</a>
	{/if}
</div>
<h1 class="inline">{$album->dirname|ucfirst}</h1>
{if $album->hasFotos()}
	<div id="gallery">
		<div class="album" data-jgallery-album-title="{$album->dirname|ucfirst}">
			<h2>{$album->dirname|ucfirst}</h2>
			{foreach from=$album->getFotos() item=foto}
				<a class="foto" href="{$foto->getResizedUrl()}" data-href="{$foto->getFullUrl()}">
					<img src="{$foto->getThumbUrl()}" alt="{$smarty.const.CSR_ROOT}{$foto->getFullUrl()|replace:"%20":" "}" />
				</a>
			{/foreach}
		</div>
		{foreach from=$album->getSubAlbums(true) item=subalbum}
			{if $subalbum->hasFotos()}
				<div class="album" data-jgallery-album-title="{$subalbum->dirname|ucfirst}">
					<h2>{$album->dirname|ucfirst}</h2>
					{foreach from=$subalbum->getFotos() item=foto}
						<a class="foto" href="{$foto->getResizedUrl()}">
							<img src="{$foto->getThumbUrl()}" alt="{$smarty.const.CSR_ROOT}{$foto->getFullUrl()|replace:"%20":" "}" />
						</a>
					{/foreach}
				</div>
			{/if}
		{/foreach}
	</div>
{else}
	<div class="subalbums">
		{foreach from=$album->getSubAlbums() item=subalbum}
			{assign var=cover_url value=$subalbum->getCoverUrl()}
			<div class="subalbum">
				<a href="{$subalbum->getUrl()}#{$cover_url|replace:"/_thumbs/":"/_resized/"}">
					<img src="{$cover_url}" alt="{$subalbum->dirname|ucfirst}" />
					<div class="subalbumname">{$subalbum->dirname|ucfirst}</div>
				</a>
			</div>
		{/foreach}
	</div>
{/if}