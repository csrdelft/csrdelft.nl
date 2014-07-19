{SimpleHtml::getMelding()}
{strip}

	{include file='MVC/forum/zoek_form.tpl'}

	{if $deel->magModereren()}
		<div id="togglemodknop" style="float: right;">
			<a class="knop" title="Moderatie-functies weergeven" onclick="$('#modereren').slideDown();
					$('#togglemodknop').toggle();
					$('#forumtabel a.forummodknop').fadeIn();">{icon get="wrench"} Modereren</a>
		</div>
	{/if}

	{capture name='navlinks'}
		<div class="forumNavigatie">
			<a href="/forum/" class="forumGrootlink">Forum</a> &raquo; <a href="/forum/deel/{$deel->forum_id}/{ForumDradenModel::instance()->getPaginaVoorDraad($draad)}#{$draad->draad_id}" class="forumGrootlink">{$deel->titel}</a>
		</div>
	{/capture}

	{capture name='titel'}
		{if $deel->magModereren()}
			<div style="display: inline-block; margin-right: 5px;">
				{if $draad->gesloten}
					<a href="/forum/wijzigen/{$draad->draad_id}/gesloten" class="knop" title="Openen (reactie mogelijk)"
					   onmouseover="$(this).children('img').attr('src', 'http://plaetjes.csrdelft.nl/famfamfam/lock_break.png');"
					   onmouseout="$(this).children('img').attr('src', 'http://plaetjes.csrdelft.nl/famfamfam/lock.png');"
					   >{icon get="lock"}</a>
				{else}
					<a href="/forum/wijzigen/{$draad->draad_id}/gesloten" class="knop" title="Sluiten (geen reactie mogelijk)"
					   onmouseover="$(this).children('img').attr('src', 'http://plaetjes.csrdelft.nl/famfamfam/lock.png');"
					   onmouseout="$(this).children('img').attr('src', 'http://plaetjes.csrdelft.nl/famfamfam/lock_open.png');"
					   >{icon get="lock_open"}</a>
				{/if}
			</div>
		{/if}
		<h1 style="display: inline-block">{$draad->titel}</h1>
	{/capture}

	{$smarty.capture.navlinks}
	{$smarty.capture.titel}

	{if $deel->magModereren()}
		{include file='MVC/forum/draad_mod.tpl'}
	{/if}

	{capture name='magreageren'}
		{if !$deel->magPosten()}
			U mag in dit deel van het forum niet reageren.
		{elseif $draad->gesloten}
			U kunt hier niet meer reageren omdat dit onderwerp gesloten is.
		{elseif $draad->verwijderd}
			<span style="color: red;">Dit onderwerp is verwijderd.</span>
		{/if}
	{/capture}
{/strip}
<table id="forumtabel">
	<tbody>
		{if ForumPostsModel::instance()->getAantalPaginas($draad->draad_id) > 1 AND !($draad->eerste_post_plakkerig AND ForumPostsModel::instance()->getHuidigePagina() != 1)}
			<tr class="tussenschot">
				<td colspan="2"></td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td>
					<i>{$smarty.capture.magreageren}</i>
					<div class="forum-paginering" style="float: right;">
						Pagina: {sliding_pager baseurl="/forum/onderwerp/"|cat:$draad->draad_id|cat:"/"
									pagecount=ForumPostsModel::instance()->getAantalPaginas($draad->draad_id) curpage=ForumPostsModel::instance()->getHuidigePagina()}
					</div>
				</td>
			</tr>
		{elseif $smarty.capture.magreageren !== ''}
			<tr>
				<td>&nbsp;</td>
				<td class="forumtekst"><i>{$smarty.capture.magreageren}</i></td>
			</tr>
		{/if}

		{assign var=vanaf value=false}
		{foreach from=$draad->getForumPosts() item=post name=posts}
			{if !$vanaf AND (!$smarty.foreach.posts.first OR ForumPostsModel::instance()->getHuidigePagina() == 1) AND
(
strtotime($post->datum_tijd) > strtotime($draad->getWanneerGelezen())
OR
strtotime($post->laatst_bewerkt) > strtotime($draad->getWanneerGelezen())
)
			}
			{assign var=vanaf value=true}
			<tr class="ongelezenvanaf" title="Ongelezen reacties vanaf hier">
				<td colspan="2">
					<a id="ongelezen"></a>
				</td>
			</tr>
			{else}
				<tr class="tussenschot">
					<td colspan="2"></td>
				</tr>
				{/if}
					{include file='MVC/forum/post_lijst.tpl'}
					{if $draad->eerste_post_plakkerig AND ForumPostsModel::instance()->getHuidigePagina() != 1 AND $smarty.foreach.posts.first}
						<tr class="tussenschot">
							<td colspan="2"></td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td>
								<div class="forum-paginering" style="float: right;">
									Pagina: {sliding_pager baseurl="/forum/onderwerp/"|cat:$draad->draad_id|cat:"/"
										pagecount=ForumPostsModel::instance()->getAantalPaginas($draad->draad_id) curpage=ForumPostsModel::instance()->getHuidigePagina()}
								</div>
							</td>
						</tr>
					{/if}
					{/foreach}

						{if ForumPostsModel::instance()->getAantalPaginas($draad->draad_id) > 1}
							<tr class="tussenschot">
								<td colspan="2"></td>
							</tr>
							<tr>
								<td>&nbsp;</td>
								<td>
									<div class="forum-paginering">
										Pagina: {sliding_pager baseurl="/forum/onderwerp/"|cat:$draad->draad_id|cat:"/"
	pagecount=ForumPostsModel::instance()->getAantalPaginas($draad->draad_id) curpage=ForumPostsModel::instance()->getHuidigePagina()}
									</div>
								</td>
							</tr>
						{/if}

						{if !$vanaf AND ForumPostsModel::instance()->getHuidigePagina() == ForumPostsModel::instance()->getAantalPaginas($draad->draad_id)}
							<tr class="ongelezenvanaf" title="Geen ongelezen berichten">
								<td colspan="2">
									<a id="ongelezen"></a>
								</td>
							</tr>
						{else}
							<tr class="tussenschot">
								<td colspan="2"></td>
							</tr>
						{/if}

						<tr>
							<td colspan="5" style="padding: 5px 0;">
								{$smarty.capture.navlinks}
								{$smarty.capture.titel}
							</td>
						</tr>

						{include file='MVC/forum/post_form.tpl'}
					</tbody>
				</table>