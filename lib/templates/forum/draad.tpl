{getMelding()}
{strip}

	{$zoekform->view()}

	{capture name='kop'}
		<div class="forumheadbtn">
			{if !isset($statistiek) AND $draad->magStatistiekBekijken()}
				<a href="/forum/onderwerp/{$draad->draad_id}/{CsrDelft\model\forum\ForumPostsModel::instance()->getHuidigePagina()}/statistiek" class="btn btn-light" title="Toon statistieken">{icon get="chart_line"}</a>
				&nbsp;&nbsp;&nbsp;
			{/if}
			<a title="Onderwerp toevoegen aan favorieten" class="btn btn-light post popup addfav" href="/menubeheer/toevoegen/favoriet">{icon get="star"}</a>
			&nbsp;&nbsp;&nbsp;
			{if $draad->isGevolgd()}
				<a href="/forum/volgenuit/{$draad->draad_id}" class="btn btn-light post ReloadPage volgenUit" title="Onderwerp niet meer volgen per email">{icon get="email_go" hover="email_delete"}</a>
			{elseif $draad->magVolgen()}
				<a href="/forum/volgenaan/{$draad->draad_id}" class="btn btn-light post ReloadPage volgenAan" title="Onderwerp volgen per email">{icon get="email" hover="email_add"}</a>
			{/if}
			&nbsp;&nbsp;&nbsp;
			{if $draad->isVerborgen()}
				<a href="/forum/tonen/{$draad->draad_id}" class="btn btn-light post ReloadPage tonenAan" title="Onderwerp tonen in zijbalk">{icon get="layout" hover="layout_add"}</a>
			{elseif $draad->magVerbergen()}
				<a href="/forum/verbergen/{$draad->draad_id}" class="btn btn-light post ReloadPage tonenUit" title="Onderwerp verbergen in zijbalk">{icon get="layout_sidebar" hover="layout_delete"}</a>
			{/if}
			&nbsp;&nbsp;&nbsp;
			{if $draad->magModereren()}
				{if $draad->gesloten}
					<a href="/forum/wijzigen/{$draad->draad_id}/gesloten" class="btn btn-light post ReloadPage slotjeUit" title="Openen (reactie mogelijk)">{icon get="lock" hover="lock_break"}</a>
				{else}
					<a href="/forum/wijzigen/{$draad->draad_id}/gesloten" class="btn btn-light post ReloadPage slotjeAan" title="Sluiten (geen reactie mogelijk)">{icon get="lock_open" hover="lock"}</a>
				{/if}
				&nbsp;&nbsp;&nbsp;
				<a class="btn btn-light modfuncties" title="Moderatie-functies weergeven" onclick="$('#forumtabel a.forummodknop').fadeIn();
						$('#modereren').slideDown();
						$(window).scrollTo('#modereren', 600, {
							easing: 'easeInOutCubic',
							offset: {
								top: -100,
								left: 0
							}
						});
				   ">{icon get="wrench"} Modereren</a>
			{/if}
		</div>

		<h1>
			{$draad->titel}
			{if isset($statistiek)}
				&nbsp;&nbsp;&nbsp;
				<span class="lichtgrijs small" title="Aantal lezers">{$draad->getAantalLezers()} lezers</span>
			{/if}
		</h1>
	{/capture}

	{$smarty.capture.kop}

	{if $draad->magModereren()}
		{include file='forum/draad_mod.tpl'}
	{/if}

	{capture name='magreageren'}
		{if $draad->verwijderd}
			<div class="draad-verwijderd">Dit onderwerp is verwijderd.</div>
		{elseif $draad->gesloten}
			<div class="draad-gesloten">
				U kunt hier niet meer reageren omdat dit onderwerp gesloten is.
				{if $draad->getForumDeel()->isOpenbaar() AND strtotime($draad->laatst_gewijzigd) < strtotime(CsrDelft\model\InstellingenModel::get('forum', 'externen_geentoegang_gesloten'))}
					<div class="dikgedrukt">Dit externe onderwerp is niet meer toegankelijk voor externen en zoekmachines.</div>
				{/if}
			</div>
		{elseif !$draad->magPosten()}
			<div class="draad-readonly">U mag in dit deel van het forum niet reageren.</div>
		{/if}
	{/capture}

{/strip}

<div class="forum-draad">
	{capture name='paginering'}
		<div class="tussenschot"></div>
		<div class="forum-paginering">
			{if $draad->pagina_per_post}
				Bericht:
			{else}
				Pagina:
			{/if}
			{if isset($statistiek)}
				{assign var="append" value="/statistiek"}
			{else}
				{assign var="append" value=""}
			{/if}
			{sliding_pager baseurl="/forum/onderwerp/"|cat:$draad->draad_id|cat:"/" url_append=$append
			pagecount=CsrDelft\model\forum\ForumPostsModel::instance()->getAantalPaginas($draad->draad_id) curpage=CsrDelft\model\forum\ForumPostsModel::instance()->getHuidigePagina()}
		</div>
	{/capture}
	{* Paginering boven eerste post op de pagina als de eerste post van het draadje niet plakkerig is of dit de eerste pagina is *}
	{if $paging AND (!$draad->eerste_post_plakkerig OR CsrDelft\model\forum\ForumPostsModel::instance()->getHuidigePagina() === 1)}
		{$smarty.capture.paginering}
	{/if}

	{assign var=vanaf value=false}
	{foreach from=$draad->getForumPosts() item=post name=posts}

		{* Als posts gewijzigd zijn zonder draad gewijzigd te triggeren voorkomt $draad->isOngelezen() dat de gele lijn wordt getoond *}
		{if !$vanaf AND $draad_ongelezen AND (!$gelezen_moment OR strtotime($post->laatst_gewijzigd) > $gelezen_moment)}
			{assign var=vanaf value=true}
			<div class="tussenschot ongelezenvanaf"><a id="ongelezen"></a></div>
		{else}
			<div class="tussenschot"></div>
		{/if}

		{include file='forum/post_lijst.tpl'}

		{* Paginering onder eerste plakkerige post op alle pagina's behalve de eerste *}
		{if $paging AND $draad->eerste_post_plakkerig AND CsrDelft\model\forum\ForumPostsModel::instance()->getHuidigePagina() != 1 AND $smarty.foreach.posts.first}
			{$smarty.capture.paginering}
		{/if}
	{/foreach}

	{* Paginering onderaan pagina *}
	{if $paging}
		{$smarty.capture.paginering}
	{/if}

	{* Geen ongelezen berichten op de laatste pagina betekend in het geheel geen ongelezen berichten *}
	{if !$vanaf AND CsrDelft\model\forum\ForumPostsModel::instance()->getHuidigePagina() === CsrDelft\model\forum\ForumPostsModel::instance()->getAantalPaginas($draad->draad_id)}
		<div class="tussenschot ongelezenvanaf"><a id="ongelezen"></a></div>
	{else}
		<div class="tussenschot"></div>
	{/if}

	<div class="magreageren">
		{$smarty.capture.magreageren}
	</div>

	<div class="forum-draad-footer">
		<div class="breadcrumbs">{$breadcrumbs}</div>
		{$smarty.capture.kop}
	</div>

	{if $draad->magPosten()}
		{include file='forum/post_form.tpl' deel=$draad->getForumDeel()}
	{/if}
</div>

{include file='forum/rss_link.tpl'}
