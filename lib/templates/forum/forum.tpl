{getMelding()}

{$zoekform->view()}

{toegang P_ADMIN}
	<div class="forumheadbtn">
		<a href="/forum/aanmaken" class="btn post popup" title="Deelforum aanmaken">{icon get="add"} </a>
	</div>
{/toegang}

{include file='forum/head_buttons.tpl'}

<h1>Forum</h1>

{foreach from=$categorien item=categorie}
	<div class="forumcategorie">
		<h3><a name="{$categorie->categorie_id}">{$categorie->titel}</a></h3>
		<div class="forumdelen">
			{foreach from=$categorie->getForumDelen() item=deel}
				<div class="forumdeel">
					<h4><a href="/forum/deel/{$deel->forum_id}">{$deel->titel}</a></h4>
					<p class="forumdeel-omschrijving">{$deel->omschrijving}</p>
				</div>
			{/foreach}
		</div>
	</div>
{/foreach}

{foreach from=CsrDelft\model\MenuModel::instance()->getMenu('remotefora')->getChildren() item=remoteCategorie}
	<div class="forumcategorie">
		<h3><a name="{$remoteCategorie->tekst}">{$remoteCategorie->tekst}</a></h3>
		<div class="forumdelen">
			{foreach from=$remoteCategorie->getChildren() item=remoteForum}
				<div class="forumdeel">
					<h4><a href="{$remoteForum->link}" target="_blank">{$remoteForum->tekst}</a></h4>
					<p class="forumdeel-omschrijving">Het forum van onze {$remoteCategorie->tekst|lcfirst} bij {$remoteForum->tekst}.</p>
				</div>
			{/foreach}
		</div>
	</div>
{/foreach}

{include file='forum/rss_link.tpl'}
