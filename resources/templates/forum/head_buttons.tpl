{toegang P_LOGGED_IN}
	<div class="forumheadbtn">
		<a href="/forum/toonalles" class="btn post confirm ReloadPage" title="Verborgen onderwerpen weer laten zien">{icon get="eye"} {CsrDelft\model\forum\ForumDradenVerbergenModel::instance()->getAantalVerborgenVoorLid()}</a>
	</div>
	<div class="forumheadbtn">
		<a href="/forum/volgniets" class="btn post confirm ReloadPage" title="Geen onderwerpen meer volgen">{icon get="email"} {CsrDelft\model\forum\ForumDradenVolgenModel::instance()->getAantalVolgenVoorLid()}</a>
	</div>
	{if !isset($deel->forum_id) OR (isset($deel->forum_id) AND $deel->magModereren())}
		<div class="forumheadbtn">
			<a href="/forum/wacht" class="btn" title="Reacties die wachten op goedkeuring">{icon get="hourglass"} {CsrDelft\model\forum\ForumPostsModel::instance()->getAantalWachtOpGoedkeuring()}</a>
		</div>
	{/if}
{/toegang}
