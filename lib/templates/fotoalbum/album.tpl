{if $loginlid->hasPermission('P_ADMIN')}
	<div style="float: right; margin: 0 0 10px 10px;">
		<a href="/actueel/fotoalbum/verwerk/" title="Verwerken">Verwerken</a>
	</div>
{/if}
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
