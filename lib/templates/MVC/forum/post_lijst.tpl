{if $post->gefilterd}
	<tr>
		<td colspan="2" class="filtered">
			<a class="weergeeflink" onclick="jQuery('#forumpost-row-{$post->post_id}').show();
					jQuery(this).remove()">
				&gt;&gt {$post->gefilterd}, klik om weer te geven. &lt;&lt;
			</a>
		</td>
	</tr>
{/if}
<tr id="forumpost-row-{$post->post_id}"{if $post->gefilterd} class="verborgen"{/if}>
	<td class="auteur">
		<table>
			<tr>
				<td>
					{$post->uid|csrnaam:'user'}
					{if LidInstellingen::get('forum', 'toonpasfotos') == 'nee'}
						<span id="t{$post->uid}-{$post->post_id}" class="togglePasfoto" title="Toon pasfoto">&raquo;</span>
					{/if}
				</td>
				<td class="postlinktd"><a href="/forum/reactie/{$post->post_id}#{$post->post_id}" id="{$post->post_id}" class="postlink" title="Link naar deze post">&rarr;</a></td>
			</tr>
		</table>
		<span class="moment">
			{if LidInstellingen::get('forum', 'datumWeergave') === 'relatief'}
				{$post->datum_tijd|reldate}
			{else}
				{$post->datum_tijd}
			{/if}
		</span>
		{if LoginModel::mag('P_LEDEN_READ')}
			<div id="p{$post->post_id}" class="forumpasfoto{if LidInstellingen::get('forum', 'toonpasfotos') == 'nee'} verborgen">{elseif LoginModel::mag('P_LEDEN_READ')}">{$post->uid|csrnaam:'pasfoto':'link'}{/if}</div>
		{/if}
		<br />
		{if $draad->belangrijk AND LoginModel::mag('P_FORUM_BELANGRIJK')}
			<span class="lichtgrijs small" title="Gelezen door lezers">{ForumDradenGelezenModel::instance()->getGelezenPercentage($post, $draad)}%</span>
		{/if}
		<br />
		{if $post->wacht_goedkeuring}
			<a href="/forum/goedkeuren/{$post->post_id}" class="knop post confirm" title="Bericht goedkeuren">goedkeuren</a>
			<br /><br />
			<a href="/tools/stats.php?ip={$post->auteur_ip}" class="knop" title="IP-log">IP-log</a>
			<a href="/forum/verwijderen/{$post->post_id}" class="knop post confirm" title="Verwijder bericht of draad">{icon get="cross"}</a>
		{else}
			<div class="forumpostKnoppen">
				{if $post->verwijderd}
					<div class="post-verwijderd">Deze reactie is verwijderd.</div>
					<a href="/forum/verwijderen/{$post->post_id}" class="knop post confirm" title="Bericht herstellen">{icon get="arrow_undo"}</a>
				{/if}
				{if LoginModel::mag('P_LOGGED_IN') AND ForumController::magPosten($draad, $deel)}
					<a href="#reageren" class="knop citeren" data-citeren="{$post->post_id}" title="Citeer bericht">{icon get="comments"}</a>
				{/if}
				{if ForumController::magForumPostBewerken($post, $draad, $deel)}
					<a href="#{$post->post_id}" class="knop
					   {if $deel->magModereren() AND $post->uid !== LoginModel::getUid() AND !$post->wacht_goedkeuring} forummodknop
					   {/if}" onclick="forumBewerken({$post->post_id});" title="Bewerk bericht">{icon get="pencil"}</a>
				{/if}
				{if $deel->magModereren()}
					<a href="/forum/offtopic/{$post->post_id}" class="knop post confirm{if !$post->wacht_goedkeuring} forummodknop{/if}" title="Offtopic markeren">{icon get="thumb_down"}</a>
					{if !$post->verwijderd}
						<a href="/forum/verwijderen/{$post->post_id}" class="knop post confirm{if !$post->wacht_goedkeuring} forummodknop{/if}" title="Verwijder bericht">{icon get="cross"}</a>
					{/if}
					<a href="/forum/afsplitsen/{$post->post_id}" class="knop post prompt{if !$post->wacht_goedkeuring} forummodknop{/if}" title="Bericht afsplitsen" data="Naam van nieuwe draad=">{icon get=arrow_branch}</a>
					<a href="/forum/verplaatsen/{$post->post_id}" class="knop post prompt{if !$post->wacht_goedkeuring} forummodknop{/if}" title="Verplaats bericht" data="Draad id={$post->draad_id}">{icon get=arrow_right}</a>
				{/if}
			</div>
		{/if}
	</td>
	<td class="bericht{cycle values="0,1"}" id="post{$post->post_id}">
		<div class="bericht">
			{$post->tekst|bbcode}
			{if $post->bewerkt_tekst}
				<div class="bewerkt clear">
					<hr />
					{$post->bewerkt_tekst|bbcode}
				</div>
			{/if}
		</div>
	</td>
</tr>