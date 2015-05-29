{getMelding()}

{$zoekform->view()}

{if LoginModel::mag('P_ADMIN')}
	<div class="forumheadbtn">
		<a href="/forum/aanmaken" class="btn post popup confirm" title="Deelforum aanmaken">{icon get="add"} </a>
	</div>
{/if}

{include file='forum/head_buttons.tpl'}

<h1>Forum{include file='forum/rss_link.tpl'}</h1>

{foreach from=$categorien item=cat}
	<div class="forumcategorie">
		<h3><a name="{$cat->categorie_id}">{$cat->titel}</a></h3>
		<div class="forumdelen">
			{foreach from=$cat->getForumDelen() item=deel}
				<div class="forumdeel bb-block col-md-2">
					<h4><a href="/forum/deel/{$deel->forum_id}">{$deel->titel}</a></h4>
					<p class="forumdeel-omschrijving">{$deel->omschrijving}</p>
				</div>
			{/foreach}
		</div>
	</div>
{/foreach}