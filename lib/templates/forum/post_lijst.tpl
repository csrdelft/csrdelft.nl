{if $post->gefilterd}
	<div class="filtered">
		<a class="weergeeflink" onclick="jQuery('#forumpost-row-{$post->post_id}').show();
			jQuery(this).remove()">
			&gt;&gt; {$post->gefilterd}, klik om weer te geven. &lt;&lt;
		</a>
	</div>
{/if}
<div id="forumpost-row-{$post->post_id}" class="forum-post{if $post->gefilterd} verborgen{/if}">
	<div class="auteur">
		<div class="postlink">
			<a href="/forum/reactie/{$post->post_id}#{$post->post_id}" id="{$post->post_id}" class="postlink"
				 title="Link naar deze post">&rarr;</a>
		</div>
		<div class="naam">
			{CsrDelft\model\ProfielModel::getLink($post->uid, 'user')}
		</div>
		{if CsrDelft\model\LidInstellingenModel::get('forum', 'toonpasfotos') == 'nee'}
			<span id="t{$post->uid}-{$post->post_id}" class="togglePasfoto" title="Toon pasfoto">&raquo;</span>
		{/if}

		<span class="moment">
			{if CsrDelft\model\LidInstellingenModel::get('forum', 'datumWeergave') === 'relatief'}
				{$post->datum_tijd|reldate}
			{else}
				{$post->datum_tijd}
			{/if}
		</span>
		{toegang P_LEDEN_READ}
		{if $post->uid !== 'x999'}
			<div class="forumpasfoto{if CsrDelft\model\LidInstellingenModel::get('forum', 'toonpasfotos') == 'nee'} verborgen{/if}">{CsrDelft\model\ProfielModel::getLink($post->uid, 'pasfoto')}</div>
		{/if}
		{/toegang}
		<br/>
		{if isset($statistiek)}
			<span class="lichtgrijs small"
						title="Gelezen door {$post->getAantalGelezen()} van de {$draad->getAantalLezers()} lezers">{$post->getGelezenPercentage()|string_format:"%.0f"}
				% gelezen</span>
		{/if}
		<br/>
		<div class="forumpostKnoppen">
			{if $post->wacht_goedkeuring}
				<a href="/forum/goedkeuren/{$post->post_id}" class="btn post confirm"
				title="Bericht goedkeuren">goedkeuren</a>
				<br/>
				<br/>
				<a href="/tools/stats.php?ip={$post->auteur_ip}" class="btn" title="IP-log">IP-log</a>
				<a href="/forum/verwijderen/{$post->post_id}" class="btn post confirm"
					 title="Verwijder bericht of draad">{icon get="cross"}</a>
				{if $post->magBewerken()}
					<a href="#{$post->post_id}"
						 class="{if $post->uid !== CsrDelft\model\security\LoginModel::getUid() AND !$post->wacht_goedkeuring} forummodknop{/if}"
						 onclick="window.forum.forumBewerken({$post->post_id});" title="Bewerk bericht">{icon get="pencil"}</a>
				{/if}
			{else}
				{if $post->verwijderd}
					<div class="post-verwijderd">Deze reactie is verwijderd.</div>
					<a href="/forum/verwijderen/{$post->post_id}" class="btn post confirm"
						 title="Bericht herstellen">{icon get="arrow_undo"}</a>
				{/if}
				{if $post->magCiteren()}
					<a href="#reageren" class="btn citeren" data-citeren="{$post->post_id}"
						 title="Citeer bericht">{icon get="comments"}</a>
				{/if}
				{if $post->magBewerken()}
					<a href="#{$post->post_id}"
						 class="{if $post->uid !== CsrDelft\model\security\LoginModel::getUid() AND !$post->wacht_goedkeuring} forummodknop{/if}"
						 onclick="window.forum.forumBewerken({$post->post_id});" title="Bewerk bericht">{icon get="pencil"}</a>
				{/if}
				{toegang P_LOGGED_IN}
				{assign var=timestamp value=strtotime($post->datum_tijd)}
					<a id="timestamp{$timestamp}" href="/forum/bladwijzer/{$post->draad_id}"
						 class="btn post forummodknop bladwijzer" data="timestamp={$timestamp}"
						 title="Bladwijzer bij dit bericht leggen">{icon get="tab"}</a>
				{/toegang}
				{if $post->getForumDraad()->magModereren()}
					<a href="/forum/offtopic/{$post->post_id}"
						 class="btn post confirm{if !$post->wacht_goedkeuring} forummodknop{/if}"
						 title="Offtopic markeren">{icon get="thumb_down"}</a>
					{if !$post->verwijderd}
						<a href="/forum/verwijderen/{$post->post_id}"
							 class="btn post confirm{if !$post->wacht_goedkeuring} forummodknop{/if}"
							 title="Verwijder bericht">{icon get="cross"}</a>
					{/if}
					<a href="/forum/verplaatsen/{$post->post_id}"
						 class="btn post prompt{if !$post->wacht_goedkeuring} forummodknop{/if}" title="Verplaats bericht"
						 data="Draad id={$post->draad_id}">{icon get=arrow_right}</a>
				{/if}
			{/if}
		</div>
	</div>
	<div class="forum-bericht bericht{cycle values="0,1"}" id="post{$post->post_id}">
		{assign var=account value=CsrDelft\model\security\AccountModel::get($post->uid)}
		{if $account AND CsrDelft\model\security\AccessModel::mag($account, 'P_ADMIN')}
			{$post->tekst|bbcode:"html"}
		{else}
			{$post->tekst|bbcode}
		{/if}
		{if $post->bewerkt_tekst}
			<div class="bewerkt clear">
				<hr/>
				{$post->bewerkt_tekst|bbcode}
			</div>
		{/if}
	</div>
</div>
