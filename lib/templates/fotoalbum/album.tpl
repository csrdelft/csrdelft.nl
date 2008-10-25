{if $lid->hasPermission('P_ADMIN')}
	<div style="float: right; margin: 0 0 10px 10px;">
		<a href="/actueel/fotoalbum/beheer/" title="Instellingen">Beheer</a>
	</div>
{/if}
<h1>Fotoalbum</h1>
{if $albums!==false}
	<h2>Albums</h2>
	{foreach from=$albums item=album}
		<a class="foto" href="{$album->getMapnaam()|urlencode}/">
			<h3>{$album->getNaam()}</h2>
		</a>
	{/foreach}
	<br />
{/if}

{if $fotos!==false}
	<h2>Foto's</h2>
	{foreach from=$fotos item=foto}
		<a class="thumb" href="{$foto->getResizedURL()}" rel="lightbox[album]">
			<img src="{$foto->getThumbURL()}" />
		</a>
	{/foreach}
{/if}