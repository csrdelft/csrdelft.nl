{$zoekform->view()}

{if isset($deel) AND $deel->magModereren() AND ForumPostsModel::instance()->getAantalWachtOpGoedkeuring($deel->forum_id) > 0}
	<div class="forumheadbtn">
		{icon get="bell"}
		<a href="/forum/wacht">Wacht op goedkeuring</a>: {ForumPostsModel::instance()->getAantalWachtOpGoedkeuring($deel->forum_id)}
	</div>
{/if}