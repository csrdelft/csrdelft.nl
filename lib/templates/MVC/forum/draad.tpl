{$view->getMelding()}

<form id="forum_zoeken" action="/forum/zoeken.php" method="post">
	<input type="text" name="zoeken" value="zoeken in forum" onfocus="this.value = '';" />
	{if $deel->magModereren()}
		<div id="btn_mod">
			<a class="knop" title="Moderatie-functies uitklappen" onclick="$('#modereren').slideDown();
					$('#btn_mod').toggle();">{icon get="bullet_wrench"} Modereren&nbsp;</a>
		</div>
	{/if}
</form>

{capture name='navlinks'}
	<div class="forumNavigatie">
		<a href="/forum/" class="forumGrootlink">Forum</a> &raquo; <strong>{$categorie->titel}</strong> &raquo;
		<a href="/forumdeel/{$deel->forum_id}" class="forumGrootlink">{$deel->titel}</a><br />
		{if $deel->magModereren()}
			<div style="display: inline-block; margin-right: 3px;">
				{if $draad->gesloten}
					<a href="/forum/draadwijzigen/{$draad->draad_id}/gesloten" class="knop" title="Openen (reactie mogelijk)"
					   onmouseover="$(this).children('img').attr('src', 'http://plaetjes.csrdelft.nl/famfamfam/lock_break.png');"
					   onmouseout="$(this).children('img').attr('src', 'http://plaetjes.csrdelft.nl/famfamfam/lock.png');"
					   >{icon get="lock"}</a>
				{else}
					<a href="/forum/draadwijzigen/{$draad->draad_id}/gesloten" class="knop" title="Sluiten (geen reactie mogelijk)"
					   onmouseover="$(this).children('img').attr('src', 'http://plaetjes.csrdelft.nl/famfamfam/lock.png');"
					   onmouseout="$(this).children('img').attr('src', 'http://plaetjes.csrdelft.nl/famfamfam/lock_open.png');"
					   >{icon get="lock_open"}</a>
				{/if}
			</div>
		{/if}
		<h1 style="display: inline-block;">{$draad->titel}</h1><br />
	</div>
{/capture}

{$smarty.capture.navlinks}

{if $deel->magModereren()}
	{include file='MVC/forum/draad_mod.tpl'}
{/if}

{capture name='magreageren'}
	{strip}
		{if !$deel->magPosten()}
			<div style="font-style: italic; padding: 5px; float: left;">
				{if $draad->gesloten}
					U kunt hier niet meer reageren omdat dit onderwerp gesloten is.
				{else}
					U mag in dit deel van het forum niet reageren.
				{/if}
			</div>
		{/if}
	{/strip}
{/capture}

{assign var=paginas value=ForumPostsModel::instance()->getAantalPaginas($draad->draad_id)}
<table id="forumtabel">
	<tbody>
		{if $paginas > 1}
			<tr class="tussenschot">
				<td colspan="2"></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					{$smarty.capture.magreageren}
					<div class="forum_paginering">
						Pagina: {sliding_pager baseurl="/forumdraad/"|cat:$draad->draad_id|cat:"/"
									pagecount=$paginas curpage=ForumPostsModel::instance()->getHuidigePagina()}
					</div>
				</td>
			</tr>
		{elseif $smarty.capture.magreageren !== ''}
			<tr>
				<td>&nbsp;</td>
				<td class="forumtekst">{$smarty.capture.magreageren}</td>
			</tr>
		{/if}
		<tr class="tussenschot">
			<td colspan="2"></td>
		</tr>

		{foreach from=$draad->getForumPosts() item=post name='berichten'}
			{include file='MVC/forum/post_lijst.tpl'}
			<tr class="tussenschot">
				<td colspan="2"></td>
			</tr>
		{/foreach}

		{if $paginas > 1}
			<tr>
				<td>&nbsp;</td>
				<td>
					<div class="forum_paginering">
						Pagina: {sliding_pager baseurl="/forumdraad/"|cat:$draad->draad_id|cat:"/"
									pagecount=$paginas curpage=ForumPostsModel::instance()->getHuidigePagina()}
					</div>
				</td>
			</tr>
			<tr class="tussenschot">
				<td colspan="2"></td>
			</tr>
		{/if}

		{* Formulier om een bericht achter te laten *}
		{include file='MVC/forum/post_form.tpl'}
	</tbody>
</table>

{$smarty.capture.navlinks}