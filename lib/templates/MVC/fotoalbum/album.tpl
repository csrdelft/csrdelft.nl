<div class="float-right">
	{if LoginModel::mag('P_ALBUM_ADD')}
		<a class="btn" href="/fotoalbum/uploaden/{$album->getSubDir()}">{icon get="picture_add"} Toevoegen</a>
		<a class="btn post modal" href="/fotoalbum/toevoegen/{$album->getSubDir()}">{icon get="folder_add"} Nieuw album</a>
	{/if}
	{if LoginModel::mag('P_LOGGED_IN') && $album->getFotos()!==false}
		<a class="btn" href="/fotoalbum/downloaden/{$album->getSubDir()}" title="Download als TAR-bestand">{icon get="picture_save"} Download album</a>
	{/if}
	{if LoginModel::mag('P_ALBUM_MOD')}
		<a class="btn" href="/fotoalbum/verwerken/{$album->getSubDir()}">{icon get="application_view_gallery"} Verwerken</a>
	{/if}
</div>

<h1>{$album->dirname|ucfirst}</h1>

{foreach from=$album->getSubAlbums() item=subalbum}
	<div class="album hoverIntent">
		{if LoginModel::mag('P_ALBUM_MOD')}
			<div class="thumb-mod">
				<a href="/fotoalbum/hernoemen/{$subalbum->getSubDir()}" class="btn post prompt ReloadPage hoverIntentContent top-right" title="Fotoalbum hernoemen" data="Nieuwe naam={$subalbum->dirname}">{icon get=pencil}</a>
			</div>
		{/if}
		<a href="{$subalbum->getUrl()}">
			<img src="{$subalbum->getThumbURL()}" />
			<div class="albumname">
				{$subalbum->dirname}
			</div>
		</a>
	</div>
{/foreach}

{foreach from=$album->getFotos() item=foto}
	<div id="{$foto->filename|md5}" class="thumb hoverIntent">
		<div class="thumb-mod">
			{if LoginModel::mag('P_ALBUM_ADD')}
				{if LoginModel::mag('P_ALBUM_MOD')}
					{if strpos($album->getSubDir(), 'Posters') === false}
						<a href="/fotoalbum/albumcover/{$album->getSubDir()}" data="cover={$foto->filename}" class="btn post confirm ReloadPage hoverIntentContent top-left" title="Instellen als albumcover">{icon get=folder_picture}</a>
					{/if}
					{if LoginModel::mag('P_ALBUM_DEL')}
						<a href="/fotoalbum/verwijderen/{$album->getSubDir()}" data="foto={$foto->filename}" class="btn post confirm hoverIntentContent top-right" title="Definitief verwijderen van deze foto">{icon get=cross}</a>
					{/if}
				{/if}
				<a href="/fotoalbum/roteren/{$album->getSubDir()}" data="foto={$foto->filename}&rotation=-90" class="btn post confirm ReloadPage hoverIntentContent bottom-left" title="Foto tegen de klok in draaien">{icon get=arrow_rotate_anticlockwise}</a>
				<a href="/fotoalbum/roteren/{$album->getSubDir()}" data="foto={$foto->filename}&rotation=90" class="btn post confirm ReloadPage hoverIntentContent bottom-right" title="Foto met de klok mee draaien">{icon get=arrow_rotate_clockwise}</a>
			{/if}
			<a href="{$foto->getResizedURL()}" rel="prettyPhoto[album]">
				<img src="{$foto->getThumbURL()}" />
			</a>
		</div>
	</div>
{/foreach}

{literal}
<script type="text/javascript">
jQuery(document).ready(function($) {
	$("a[rel^='prettyPhoto']").prettyPhoto({
	theme: 'dark_rounded',
			markup: '<div class="pp_pic_holder"> \
		<div class="ppt">&nbsp;</div> \
		<div class="pp_top"> \
			<div class="pp_left"></div> \
			<div class="pp_middle"></div> \
			<div class="pp_right"></div> \
		</div> \
		<div class="pp_content_container"> \
			<div class="pp_left"> \
			<div class="pp_right"> \
				<div class="pp_content"> \
					<div class="pp_loaderIcon"></div> \
					<div class="pp_fade"> \
						<a href="#" class="pp_expand" title="Expand the image">Expand</a> \
						<div class="pp_hoverContainer"> \
							<a class="pp_next" href="#">next</a> \
							<a class="pp_previous" href="#">previous</a> \
						</div> \
						<div id="pp_full_res"></div> \
						<div class="pp_details clearfix"> \
							<a id="fullsizeLink"><img src="http://plaetjes.csrdelft.nl/famfamfam/disk.png" title="Origineel downloaden" /></a> \
							<p class="pp_description"></p> \
							<!--<a class="pp_close" href="#">Close</a>--> \
							<div class="pp_nav"> \
								<a href="#" class="pp_arrow_previous">Previous</a> \
								<p class="currentTextHolder">0/0</p> \
								<a href="#" class="pp_arrow_next">Next</a> \
							</div> \
						</div> \
					</div> \
				</div> \
			</div> \
			</div> \
		</div> \
		<div class="pp_bottom"> \
			<div class="pp_left"></div> \
			<div class="pp_middle"></div> \
			<div class="pp_right"></div> \
		</div> \
	</div> \
	<div class="pp_overlay"></div>',
			changepicturecallback: function() {

				imgurl = $('#fullResImage').attr('src');
				$('#fullsizeLink').attr('href', imgurl.replace('_resized/', ''));

				urlparts = imgurl.split('/');
				window.location.hash = '#' + urlparts[urlparts.length - 1];

			},
			callback: function() {
				return false;
			}
		});

		//open de foto waar naar gelinkt wordt met de hashtag
		if (window.location.hash != '') {
			imgname = window.location.hash.substring(1);

			//verzamel alle plaatjes.
			var pics = $('a[rel*=prettyPhoto]');
			var urls = pics.map(function() {
				return $(this).attr('href')
			});
			var titles = pics.map(function() {
				return $(this).attr('title')
			});

			$.prettyPhoto.open(urls, null, titles);

			//welke moeten we openen?
			indexoftheimage = 0;
			pics.each(function(index, value) {
				if (pics[index].title == imgname) {
					$.prettyPhoto.changePage(index);
				}
			});
		}
	});
</script>
{/literal}