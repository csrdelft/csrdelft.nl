{if $lid->hasPermission('P_ADMIN')}
	<div style="float: right; margin: 0 0 10px 10px;">
		<a href="/actueel/fotoalbum/verwerk/" title="Verwerken">Verwerken</a>
	</div>
{/if}
{$album->getBreadcrumb()}
<h1>{$album->getNaam()}</h1>
{if $album->getSubAlbums()!==false}
	<h2>Albums</h2>
	{foreach from=$album->getSubAlbums() item=subalbum}
		<a href="{$subalbum->getMapnaam()|urlencode}/">
			<h3>{$subalbum->getNaam()}</h2>
		</a>
	{/foreach}
	<br />
{/if}

{if $album->getFotos()!==false}
	{foreach from=$album->getFotos() item=foto}
		<a class="thumb" href="{$foto->getResizedURL()}" rel="lightbox[album]">
			<img src="{$foto->getThumbURL()}" />
		</a>
	{/foreach}
{/if}