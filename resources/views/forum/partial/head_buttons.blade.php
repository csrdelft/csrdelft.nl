@can('P_LOGGED_IN')
	<div class="forumheadbtn">
		<a href="/forum/toonalles" class="btn btn-light post confirm ReloadPage"
			 title="Verborgen onderwerpen weer laten zien">@icon('eye') {{CsrDelft\model\forum\ForumDradenVerbergenModel::instance()->getAantalVerborgenVoorLid()}}</a>
	</div>
	<div class="forumheadbtn">
		<a href="/forum/volgniets" class="btn btn-light post confirm ReloadPage" title="Geen onderwerpen meer volgen">@icon('email') {{CsrDelft\model\forum\ForumDradenVolgenModel::instance()->getAantalVolgenVoorLid()}}</a>
	</div>
	@if(!isset($deel->forum_id) || (isset($deel->forum_id) && $deel->magModereren()))
		<div class="forumheadbtn">
			<a href="/forum/wacht" class="btn btn-light"
				 title="Reacties die wachten op goedkeuring">@icon('hourglass') {{CsrDelft\model\forum\ForumPostsModel::instance()->getAantalWachtOpGoedkeuring()}}</a>
		</div>
	@endif
@endcan
