<h1 class="inline">{$album->dirname|ucfirst}</h1>
<div class="float-right" style="margin-top: 30px;">
	{if LoginModel::mag('P_ALBUM_ADD')}
		<a class="btn" href="/fotoalbum/uploaden/{$album->getSubDir()}">{icon get="picture_add"} Toevoegen</a>
		<a class="btn post popup" href="/fotoalbum/toevoegen/{$album->getSubDir()}">{icon get="folder_add"} Nieuw album</a>
	{/if}
	{if LoginModel::mag('P_ALBUM_MOD')}
		<a href="/fotoalbum/hernoemen/{$album->getSubDir()}" class="btn post prompt ReloadPage" title="Fotoalbum hernoemen" data="Nieuwe naam={$album->dirname|ucfirst}">{icon get=pencil} Naam wijzigen</a>
		{if $album->isEmpty()}
			<a href="/fotoalbum/verwijderen/{$album->getSubDir()}" class="btn post confirm ReloadPage" title="Fotoalbum verwijderen">{icon get=cross}</a>
		{/if}
		<a class="btn" href="/fotoalbum/verwerken/{$album->getSubDir()}">{icon get="application_view_gallery"} Verwerken</a>
	{/if}
	{if LoginModel::mag('P_LOGGED_IN') && $album->getFotos()!==false}
		<a class="btn" href="/fotoalbum/downloaden/{$album->getSubDir()}" title="Download als TAR-bestand">{icon get="picture_save"} Download album</a>
	{/if}
</div>
<div class="subalbums">
	{foreach from=$album->getSubAlbums() item=subalbum}
		<div class="album">
			<a href="{$subalbum->getUrl()}" title="{$subalbum->getUrl()|replace:"%20":" "}">
				<img src="{$subalbum->getThumbURL()}" alt="{$subalbum->dirname|ucfirst}" />
				<div class="albumname">{$subalbum->dirname|ucfirst}</div>
			</a>
		</div>
	{/foreach}
</div>
<div id="gallery">
	{if $album->hasFotos()}
		<div class="album" data-jgallery-album-title="{$album->dirname|ucfirst}">
			{foreach from=$album->getFotos() item=foto}
				<a href="{$foto->getResizedUrl()}">
					<img src="{$foto->getThumbUrl()}" alt="{$foto->getFullUrl()|replace:"%20":" "}" />
				</a>
			{/foreach}
		</div>
		{*foreach from=$album->getSubAlbums() item=subalbum}
		{if $subalbum->hasFotos()}
		<div class="album" data-jgallery-album-title="{$subalbum->dirname|ucfirst}">
		{foreach from=$subalbum->getFotos() item=foto}
		<a href="{$foto->getResizedUrl()}">
		<img src="{$foto->getThumbUrl()}" alt="{$foto->getFullUrl()|replace:"%20":" "}" />
		</a>
		{/foreach}
		</div>
		{/if}
		{/foreach*}
		<script type="text/javascript" src="/layout/js/jquery/plugins/jgallery.js?v=1.4.1"></script>
		<script type="text/javascript">
			try {
				$(function () {
					$('#gallery').jGallery({
						"height": "897px",
						"mode": "standard",
						"canChangeMode": false,
						"canZoom": false,
						"backgroundColor": "FFFFFF",
						"textColor": "000000",
						"thumbType": "image",
						"thumbWidth": 150,
						"thumbHeight": 150,
						"thumbWidthOnFullScreen": 150,
						"thumbHeightOnFullScreen": 150,
						"thumbnailsPosition": "bottom",
						"hideThumbnailsOnInit": false,
						"canMinimalizeThumbnails": false,
						"transition": "moveToLeft_moveFromRight",
						"transitionBackward": "moveToRight_moveFromLeft",
						"transitionCols": "1",
						"transitionRows": "1",
						"title": true,
						"titleExpanded": false,
						"tooltipSeeAllPhotos": "Grid",
						"tooltipSeeOtherAlbums": "Toon sub-albums"
					});
					$('div.title').off();
					$('div.title').on('click', function (event) {
						selectText(this);
					});
					// grid scrollbar
					$('#gallery').find('span.full-screen').on('click', function () {
						$('#gallery').find('div.jgallery-container-inner').css({
							"height": $(this).parent().height() + 'px',
							"overflow-y": "auto"
						});

						$('#gallery').find('span.jgallery-close').one('click', function () {
							$('#gallery').find('div.jgallery-container-inner').css({
								"height": "",
								"overflow-y": ""
							});
						});
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
</div>