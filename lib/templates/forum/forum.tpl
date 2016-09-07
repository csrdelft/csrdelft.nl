{getMelding()}

{$zoekform->view()}

{if LoginModel::mag('P_ADMIN')}
	<div class="forumheadbtn">
		<a href="/forum/aanmaken" class="btn post popup confirm" title="Deelforum aanmaken">{icon get="add"} </a>
	</div>
{/if}

{include file='forum/head_buttons.tpl'}

<h1>Forum{include file='forum/rss_link.tpl'}</h1>

{foreach from=$categorien item=categorie}
	<div class="forumcategorie">
		<h3><a name="{$categorie->id}">{$categorie->titel}</a></h3>
		<div class="forumdelen">
			{foreach from=$categorie->getForumDelen() item=forum}
				<div class="forumdeel bb-block col-md-2">
					<h4><a href="/forum/deel/{$forum->id}">{$forum->titel}</a></h4>
					<p class="forumdeel-omschrijving">{$forum->omschrijving}</p>
				</div>
			{/foreach}
		</div>
	</div>
{/foreach}

{foreach from=MenuModel::instance()->getMenu('remotefora')->getChildren() item=remoteCategorie}
	<div class="forumcategorie">
		<h3><a name="{$remoteCategorie->tekst}">{$remoteCategorie->tekst}</a></h3>
		<div class="forumdelen">
			{foreach from=$remoteCategorie->getChildren() item=remoteForum}
				<div class="forumdeel bb-block col-md-2">
					<h4><a href="{$remoteForum->link}" target="_blank">{$remoteForum->tekst}</a></h4>
					<p class="forumdeel-omschrijving">Het forum van onze {$remoteCategorie->tekst|lcfirst} bij {$remoteForum->tekst}.</p>
				</div>
			{/foreach}
		</div>
	</div>
{/foreach}