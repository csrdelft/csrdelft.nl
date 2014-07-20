<form id="forum_zoeken" action="/forum/zoeken" method="post" class="hoverIntent">
	<input type="text" name="zoeken" placeholder="Zoeken in forum" />
	<div class="hoverIntentContent">
		<input id="titelzoeken" name="titelzoeken" type="checkbox" /><label for="titel">Alleen op titel zoeken</label>
		<p>
			<select id="datumzoeken" name="datumzoeken">
				<option value="ouder">Wel</option>
				<option value="jonger">Niet</option>
			</select>
			ouder dan
			<select id="jaarzoeken" name="jaarzoeken">
				<option value="1">1</option>
				<option value="2">2</option>
				<option value="3">3</option>
			</select>
			jaar oud
		</p>
		auteur:
	</div>
</form>

{if isset($deel) AND $deel->magModereren() AND ForumPostsModel::instance()->getAantalWachtOpGoedkeuring($deel->forum_id) > 0}
	<div class="forumheadbtn">
		{icon get="bell"}
		<a href="/forum/wacht">Wacht op goedkeuring</a>: {ForumPostsModel::instance()->getAantalWachtOpGoedkeuring($deel->forum_id)}
	</div>
{/if}