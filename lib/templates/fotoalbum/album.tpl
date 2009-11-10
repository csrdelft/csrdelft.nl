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
			{$subalbum->getNaam()}
		</a>
	{/foreach}
	<br />
{/if}

{if $album->getFotos()!==false}
	{foreach from=$album->getFotos() item=foto}
		<a class="thumb" href="{$foto->getResizedURL()}" rel="lightbox[album]" title="{$foto->getBestandsnaam()}">
			<img src="{$foto->getThumbURL()}" alt="{$foto->getBestandsnaam()}" />
		</a>
	{/foreach}
{/if}
