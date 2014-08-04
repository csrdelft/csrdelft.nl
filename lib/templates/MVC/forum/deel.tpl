{SimpleHtml::getMelding()}

{$zoekform->view()}

{if isset($deel->forum_id)}
	{if LoginLid::mag('P_ADMIN')}
		<div class="forumheadbtn">
			<a href="/forum/beheren/{$deel->forum_id}" class="knop post popup" title="Deelforum beheren">{icon get="wrench_orange"} Beheren</a>
		</div>
	{/if}
	{if $deel->magModereren() AND !$prullenbak}
		<div class="forumheadbtn">
			<a href="/forum/deel/{$deel->forum_id}/prullenbak" class="knop">{icon get="bin_closed"} Prullenbak</a>
		</div>
	{/if}
{else}
	{if $verborgen_aantal > 0}
		<div class="forumheadbtn">
			<a href="/forum/herstel" class="knop confirm" title="Verborgen onderwerpen weer laten zien">{icon get="eye"} {$verborgen_aantal}</a>
		</div>
	{/if}
{/if}

{capture name='navlinks'}
	<div class="forumNavigatie">
		<a href="/forum" class="forumGrootlink">Forum</a>
		{if $categorien}
			&raquo;
			<select name="forum_id" style="padding: 0;" onchange="document.location.href = '/forum/' + this.value;">
				<option value="recent"{if 0 === $deel->forum_id} selected="selected"{/if}>Recent gewijzigd</option>
				{foreach from=$categorien item=cat}
					<optgroup label="{$cat->titel}">
						{foreach from=$cat->getForumDelen() item=newDeel}
							<option value="deel/{$newDeel->forum_id}"{if $newDeel->forum_id === $deel->forum_id} selected="selected"{/if}>{$newDeel->titel}</option>
						{/foreach}
					</optgroup>
				{/foreach}
			</select>
		{/if}
	</div>
{/capture}

{$smarty.capture.navlinks}
<h1>{$deel->titel}</h1>

<table id="forumtabel">
	<thead>
		<tr>
			<th colspan="2">Titel</th>
			<th>Reacties</th>
			<th style="text-align: center;">Auteur</th>
			<th>Recente wijziging</th>
		</tr>
	</thead>
	<tbody>
		{if !$deel->hasForumDraden()}
			<tr>
				<td colspan="5">Dit forum is nog leeg.</td>
			</tr>
		{/if}
		{foreach from=$deel->getForumDraden($wacht, $prullenbak, $belangrijk) item=draad}
			{include file='MVC/forum/draad_lijst.tpl'}
		{/foreach}
	</tbody>
	<thead>
		<tr>
			<th colspan="5">
				{if isset($deel->forum_id)}
					{sliding_pager baseurl="/forum/deel/"|cat:$deel->forum_id|cat:"/"
							pagecount=ForumDradenModel::instance()->getAantalPaginas($deel->forum_id) curpage=ForumDradenModel::instance()->getHuidigePagina()
							separator=" &nbsp;" show_prev_next=true}
				{else}
					{if $belangrijk}
						{assign var="belangrijk" value="/belangrijk"}
					{/if}
					{sliding_pager baseurl="/forum/recent/" url_append=$belangrijk
							pagecount=ForumDradenModel::instance()->getHuidigePagina() curpage=ForumDradenModel::instance()->getHuidigePagina()
							separator=" &nbsp;"}
					&nbsp;<a href="/forum/recent/{ForumDradenModel::instance()->getAantalPaginas(0)}{$belangrijk}">verder terug</a>
				{/if}
			</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td colspan="5">
				<div class="forumdeel-omschrijving">
					<div style="float: right;">{$smarty.capture.navlinks}</div>
					<h1>{$deel->titel}</h1>
					{$deel->omschrijving}
				</div>
			</td>
		</tr>
		{if $deel->magPosten()}
			{include file='MVC/forum/draad_form.tpl'}
		{/if}
	</tbody>
</table>