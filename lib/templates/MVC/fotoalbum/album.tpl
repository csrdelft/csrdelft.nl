<h1 class="inline">{$album->dirname|ucfirst}</h1>
<div class="float-right" style="margin-top: 30px;">
	{if LoginModel::mag('P_ALBUM_ADD')}
		<a class="btn" href="/fotoalbum/uploaden/{$album->getSubDir()}">{icon get="picture_add"} Toevoegen</a>
		<a class="btn post popup" href="/fotoalbum/toevoegen/{$album->getSubDir()}">{icon get="folder_add"} Nieuw album</a>
	{/if}
	{if LoginModel::mag('P_ALBUM_MOD')}
		<a href="/fotoalbum/hernoemen/{$album->getSubDir()}" class="btn post prompt ReloadPage" title="Fotoalbum hernoemen" data="Nieuwe naam={$album->dirname}">{icon get=pencil} Naam wijzigen</a>
		{if $album->isEmpty()}
			<a href="/fotoalbum/verwijderen/{$album->getSubDir()}" class="btn post confirm ReloadPage" title="Fotoalbum verwijderen">{icon get=cross}</a>
		{/if}
		<a class="btn" href="/fotoalbum/verwerken/{$album->getSubDir()}">{icon get="application_view_gallery"} Verwerken</a>
	{/if}
	{if LoginModel::mag('P_LOGGED_IN') && $album->getFotos()!==false}
		<a class="btn" href="/fotoalbum/downloaden/{$album->getSubDir()}" title="Download als TAR-bestand">{icon get="picture_save"} Download album</a>
	{/if}
</div>
<div id="gallery">
	{if !$album->hasFotos()}
		{foreach from=$album->getSubAlbums() item=subalbum}
			<div class="album">
				<a href="{$subalbum->getUrl()}" title="{$subalbum->getUrl()|replace:"%20":" "}">
					<img src="{$subalbum->getThumbURL()}" alt="{$subalbum->dirname}" />
					<div class="albumname">{$subalbum->dirname}</div>
				</a>
			</div>
		{/foreach}
	{else}
		<div class="album" data-jgallery-album-title="{$album->dirname|ucfirst}">
			{foreach from=$album->getFotos() item=foto}
				<a href="{$foto->getResizedUrl()}">
					<img src="{$foto->getThumbUrl()}" alt="{$foto->getFullUrl()|replace:"%20":" "}" />
				</a>
			{/foreach}
		</div>
		{foreach from=$album->getSubAlbums() item=subalbum}
			<div class="album" data-jgallery-album-title="{$subalbum->dirname|ucfirst}">
				{foreach from=$subalbum->getFotos() item=foto}
					<a href="{$foto->getResizedUrl()}">
						<img src="{$foto->getThumbUrl()}" alt="{$foto->getFullUrl()|replace:"%20":" "}" />
					</a>
				{/foreach}
			</div>
		{/foreach}
		<script type="text/javascript">
			$(document).ready(function () {
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
			});
		</script>
	{/if}
</div>