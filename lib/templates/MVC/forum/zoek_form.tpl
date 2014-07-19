<form id="forum_zoeken" action="/forum/zoeken" method="post"><input type="text" name="zoeken" placeholder="Zoeken in forum" /></form>

{if isset($deel) AND $deel->magModereren() AND ForumPostsModel::instance()->getAantalWachtOpGoedkeuring($deel->forum_id) > 0}
	<div style="float: right; margin-right: 50px;">
		{icon get="bell"}
		<a href="/forum/wacht">Wacht op goedkeuring</a>: {ForumPostsModel::instance()->getAantalWachtOpGoedkeuring($deel->forum_id)}
	</div>
{/if}