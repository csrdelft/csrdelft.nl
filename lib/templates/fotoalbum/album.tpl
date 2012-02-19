<div style="float: right; margin: 0 0 10px 10px;">
	{if $loginlid->hasPermission('P_LOGGED_IN')}
		<a href="/actueel/fotoalbum/toevoegen/" title="Toevoegen">Toevoegen</a>
	{/if}
	{if $loginlid->hasPermission('P_LOGGED_IN') && $album->getFotos()!==false}
		| <a href="/tools/downloadalbum.php?album={$album->getPad()}" title="Download als TAR-bestand">Download album</a>
	{/if}
	{if $loginlid->hasPermission('P_ADMIN')}
		| <a href="/actueel/fotoalbum/verwerk/" title="Verwerken">Verwerken</a>
	{/if}
</div>

{$album->getBreadcrumb()}
<h1>{$album->getNaam()}</h1>
{if $album->getSubAlbums()!==false}
	{foreach from=$album->getSubAlbums() item=subalbum}
		<a class="album" href="{$subalbum->getMapnaam()|urlencode}/">
			<img src="{$subalbum->getThumbURL()}" />
			<span class="albumname">{$subalbum->getNaam()}</span>
		</a>
	{/foreach}
{/if}

{if is_array($album->getFotos())}
	{if count($album->getFotos())>0}
		{foreach from=$album->getFotos() item=foto}
			<a class="thumb" href="{$foto->getResizedURL()}" rel="prettyPhoto[album]" title="{$foto->getBestandsnaam()}">
				<img src="{$foto->getThumbURL()}" alt="{$album->getNaam()} - {$foto->getBestandsnaam()}" />
			</a>
		{/foreach}
	{else}{* geen foto's in dit album *}{/if}
{else}
	Album bestaat niet.
{/if}

{literal}
<script type="text/javascript">
jQuery(document).ready(function($){
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
		changepicturecallback: function(){

			imgurl=$('#fullResImage').attr('src');
			$('#fullsizeLink').attr('href', imgurl.replace('_resized/', ''));

			urlparts=imgurl.split('/');
			window.location.hash='#'+urlparts[urlparts.length-1];

		},
		callback: function(){
			return false;
		}
	});

	//open de foto waar naar gelinkt wordt met de hashtag.
	if(window.location.hash!=''){
		imgname=window.location.hash.substring(1);

		//verzamel alle plaatjes.
		var pics = $('a[rel*=prettyPhoto]');
		var urls = pics.map(function() { return $(this).attr('href') });
		var titles = pics.map(function() { return $(this).attr('title') });

		$.prettyPhoto.open(urls, null, titles);

		//welke moeten we openen?
		indexoftheimage=0;
		pics.each(function(index, value){
			if(pics[index].title==imgname){
				$.prettyPhoto.changePage(index);
			}
		});
	}
});
</script>
{/literal}
