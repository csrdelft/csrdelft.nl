{getMelding()}
{strip}

	{$zoekform->view()}

	{capture name='kop'}
		<div class="forumheadbtn">
			{if $draad->isVerborgen()}
				<a href="/forum/tonen/{$draad->draad_id}" class="knop round post ReloadPage" title="Onderwerp tonen in zijbalk"
				   onmouseover="$(this).children('img').attr('src', '{$CSR_PICS}/famfamfam/layout_add.png');"
				   onmouseout="$(this).children('img').attr('src', '{$CSR_PICS}/famfamfam/layout.png');"
				   >{icon get="layout"}</a>
			{elseif $draad->magVerbergen()}
				<a href="/forum/verbergen/{$draad->draad_id}" class="knop round post ReloadPage" title="Onderwerp verbergen in zijbalk"
				   onmouseover="$(this).children('img').attr('src', '{$CSR_PICS}/famfamfam/layout_delete.png');"
				   onmouseout="$(this).children('img').attr('src', '{$CSR_PICS}/famfamfam/layout_sidebar.png');"
				   >{icon get="layout_sidebar"}</a>
			{/if}
			&nbsp;&nbsp;&nbsp;
			{if $draad->isGevolgd()}
				<a href="/forum/volgenuit/{$draad->draad_id}" class="knop round post ReloadPage" title="Onderwerp niet meer volgen per email"
				   onmouseover="$(this).children('img').attr('src', '{$CSR_PICS}/famfamfam/email_delete.png');"
				   onmouseout="$(this).children('img').attr('src', '{$CSR_PICS}/famfamfam/email_go.png');"
				   >{icon get="email_go"}</a>
			{elseif $draad->magVolgen()}
				<a href="/forum/volgenaan/{$draad->draad_id}" class="knop round post ReloadPage" title="Onderwerp volgen per email"
				   onmouseover="$(this).children('img').attr('src', '{$CSR_PICS}/famfamfam/email_add.png');"
				   onmouseout="$(this).children('img').attr('src', '{$CSR_PICS}/famfamfam/email.png');"
				   >{icon get="email"}</a>
			{/if}
			&nbsp;&nbsp;&nbsp;
			{if $deel->magModereren()}
				{if $draad->gesloten}
					<a href="/forum/wijzigen/{$draad->draad_id}/gesloten" class="knop round post ReloadPage" title="Openen (reactie mogelijk)"
					   onmouseover="$(this).children('img').attr('src', '{$CSR_PICS}/famfamfam/lock_break.png');"
					   onmouseout="$(this).children('img').attr('src', '{$CSR_PICS}/famfamfam/lock.png');"
					   >{icon get="lock"}</a>
				{else}
					<a href="/forum/wijzigen/{$draad->draad_id}/gesloten" class="knop round post ReloadPage" title="Sluiten (geen reactie mogelijk)"
					   onmouseover="$(this).children('img').attr('src', '{$CSR_PICS}/famfamfam/lock.png');"
					   onmouseout="$(this).children('img').attr('src', '{$CSR_PICS}/famfamfam/lock_open.png');"
					   >{icon get="lock_open"}</a>
				{/if}
				&nbsp;&nbsp;&nbsp;
				<a class="knop" title="Moderatie-functies weergeven" onclick="$('#modereren').slideDown();
						$.scrollTo('#modereren', 600, {
							easing: 'easeInOutCubic'
						});
						$('#forumtabel a.forummodknop').fadeIn();">{icon get="wrench"} Modereren</a>
			{/if}
		</div>
		<div class="breadcrumbs">
			<a href="/forum/">Forum</a> &raquo; <a href="/forum/deel/{$deel->forum_id}/{ForumDradenModel::instance()->getPaginaVoorDraad($draad)}#{$draad->draad_id}">{$deel->titel}</a>
		</div>
		<h1>
			{$draad->titel}
			{if $draad->belangrijk AND LoginModel::mag('P_FORUM_BELANGRIJK')}
				<span class="lichtgrijs small" title="Aantal lezers"> {count($draad->getLezers())}</span>
			{/if}
		</h1>
	{/capture}

	{$smarty.capture.kop}

	{if $deel->magModereren()}
		{include file='MVC/forum/draad_mod.tpl'}
	{/if}

	{capture name='magreageren'}
		{if $draad->verwijderd}
			<div class="draad-verwijderd">Dit onderwerp is verwijderd.</div>
		{elseif $draad->gesloten}
			<div class="draad-gesloten">U kunt hier niet meer reageren omdat dit onderwerp gesloten is.</div>
		{elseif !ForumController::magPosten($draad, $deel)}
			<div class="draad-readonly">U mag in dit deel van het forum niet reageren.</div>
		{/if}
	{/capture}

{/strip}

<table id="forumtabel">
	<tbody>

		{if $smarty.capture.magreageren !== ''}
			<tr>
				<td>&nbsp;</td>
				<td class="forumtekst">{$smarty.capture.magreageren}</td>
			</tr>
		{/if}

		{capture name='paginering'}
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
		{/capture}

		{* Paginering boven eerste post op de pagina als de eerste post van het draadje niet plakkerig is of dit de eerste pagina is *}
		{if $paging AND (!$draad->eerste_post_plakkerig OR ForumPostsModel::instance()->getHuidigePagina() === 1)}
			{$smarty.capture.paginering}
		{/if}

		{assign var=vanaf value=false}
		{foreach from=$draad->getForumPosts() item=post name=posts}

			{if !$vanaf AND $draad->onGelezen() AND strtotime($post->laatst_gewijzigd) > strtotime($draad->getWanneerGelezen()->datum_tijd)}
				{* als posts gewijzigd zijn zonder draad gewijzigd te triggeren voorkomt $draad->onGelezen() dat de gele lijn wordt getoont *}
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

			{* Paginering onder eerste plakkerige post op alle pagina's behalve de eerste *}
			{if $paging AND $draad->eerste_post_plakkerig AND ForumPostsModel::instance()->getHuidigePagina() != 1 AND $smarty.foreach.posts.first}
				{$smarty.capture.paginering}
			{/if}

		{/foreach}

		{* Paginering onderaan pagina *}
		{if $paging}
			{$smarty.capture.paginering}
		{/if}

		{* Geen ongelezen berichten op de laatste pagina betekend in het geheel geen ongelezen berichten *}
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

		{if $smarty.capture.magreageren !== ''}
			<tr>
				<td>&nbsp;</td>
				<td class="forumtekst">{$smarty.capture.magreageren}</td>
			</tr>
		{/if}

		<tr>
			<td colspan="2" class="forumdraadtitelbottom">
				<br />
				{$smarty.capture.kop}
			</td>
		</tr>

	</tbody>
</table>

{if ForumController::magPosten($draad, $deel)}
	{include file='MVC/forum/post_form.tpl'}
{/if}