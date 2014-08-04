{SimpleHtml::getMelding()}
{strip}

	{$zoekform->view()}

	{capture name='titel'}
		<div class="forumheadbtn">
			{if $draad->magVerbergen()}
				{if $draad->isVerborgen()}
					<a href="/forum/optin/{$draad->draad_id}" class="knop" title="Onderwerp tonen in zijbalk">{icon get="eye"}</a>
				{else}
					<a href="/forum/optout/{$draad->draad_id}" class="knop" title="Onderwerp verbergen in zijbalk">{icon get="application_side_list"}</a>
				{/if}
				&nbsp;&nbsp;&nbsp;
			{/if}
			{if $deel->magModereren()}
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
				&nbsp;&nbsp;&nbsp;
				<a class="knop" title="Moderatie-functies weergeven" onclick="$('#modereren').slideDown();
					$.scrollTo('#modereren', 600, { easing: 'easeInOutCubic' });
					$('#forumtabel a.forummodknop').fadeIn();">{icon get="wrench"} Modereren</a>
			{/if}
		</div>
		<div class="forumNavigatie">
			<a href="/forum/" class="forumGrootlink">Forum</a> &raquo; <a href="/forum/deel/{$deel->forum_id}/{ForumDradenModel::instance()->getPaginaVoorDraad($draad)}#{$draad->draad_id}" class="forumGrootlink">{$deel->titel}</a>
		</div>
		<h1>{$draad->titel}</h1>
	{/capture}

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
		{if $paging AND !($draad->eerste_post_plakkerig AND ForumPostsModel::instance()->getHuidigePagina() != 1)}
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
			{if !$vanaf AND
(
strtotime($post->datum_tijd) > strtotime($draad->getWanneerGelezen()->datum_tijd)
OR
strtotime($post->laatst_bewerkt) > strtotime($draad->getWanneerGelezen()->datum_tijd)
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
			{if $paging AND $draad->eerste_post_plakkerig AND ForumPostsModel::instance()->getHuidigePagina() != 1 AND $smarty.foreach.posts.first}
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

		{if $paging}
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
		{if !$vanaf AND ForumPostsModel::instance()->getHuidigePagina() === ForumPostsModel::instance()->getAantalPaginas($draad->draad_id)}
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
				{$smarty.capture.titel}
			</td>
		</tr>

		{include file='MVC/forum/post_form.tpl'}
	</tbody>
</table>