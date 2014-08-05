{if $draad->magVerbergen()}
	<div class="forumheadbtn">
		<a href="/forum/toonalles" class="knop confirm" title="Verborgen onderwerpen weer laten zien">{icon get="eye"} {ForumDradenVerbergenModel::instance()->getAantalVerborgenVoorLid()}</a>
	</div>
{/if}
{if $draad->magVolgen()}
	<div class="forumheadbtn">
		<a href="/forum/volgniets" class="knop confirm" title="Geen onderwerpen meer volgen">{icon get="email"} {ForumDradenVolgenModel::instance()->getAantalVolgenVoorLid()}</a>
	</div>
{/if}