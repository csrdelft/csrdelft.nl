<tr id="forumpost-row-{$post->post_id}">
	<td class="auteur">
		<a href="/forum/reactie/{$post->post_id}#{$post->post_id}" id="{$post->post_id}" class="postlink" title="Link naar deze post">&rarr;</a>
		{$post->lid_id|csrnaam:'user':'visitekaartje'}
		{if $loginlid->hasPermission('P_LEDEN_READ')}
			<span tabindex="0" id="t{$post->lid_id}-{$post->post_id}" class="togglePasfoto"{if $loginlid->getInstelling('forum_toonpasfotos') == 'nee'} title="Toon pasfoto">&raquo;{else}>{/if}</span>
		{/if}<br />
		<div id="p{$post->post_id}" class="forumpasfoto{if $loginlid->getInstelling('forum_toonpasfotos') == 'nee'} verborgen">{elseif $loginlid->hasPermission('P_LEDEN_READ')}">{$post->lid_id|csrnaam:'pasfoto'}{/if}</div>
		<span class="moment">
			{if $loginlid->getInstelling('forum_datumWeergave') === 'relatief'}
				{$post->datum_tijd|reldate}
			{else}
				{$post->datum_tijd}
			{/if}
		</span>
		<div class="forumpostKnoppen">
			{if !$draad->gesloten AND $deel->magPosten()}
				<a href="#reageren" class="knop" onclick="forumCiteren({$post->post_id});" title="Citeer bericht">{icon get="comments"}</a>
			{/if}
			{if (($deel->magPosten() AND !$draad->gesloten AND $post->lid_id === $loginlid->getUid() AND $loginlid->hasPermission('P_LOGGED_IN')) OR $deel->magModereren())}
				<a href="#{$post->post_id}" class="knop{if $deel->magModereren() AND $post->lid_id !== $loginlid->getUid()} forummodknop{/if}" onclick="forumBewerken({$post->post_id});" title="Bewerk bericht">{icon get="pencil"}</a>
			{/if}
			{if $deel->magModereren()}
				<a href="/forum/offtopic/{$post->post_id}" class="knop post confirm forummodknop" title="Offtopic markeren">{icon get="thumb_down"}</a>
				<a href="/forum/verwijderen/{$post->post_id}" class="knop post confirm forummodknop" title="Verwijder bericht">{icon get="cross"}</a>
				{if $post->wacht_goedkeuring}
					<a href="/forum/goedkeuren/{$post->post_id}" class="knop post confirm" title="Bericht goedkeuren">goedkeuren</a>
					<a href="/tools/stats.php?ip={$post->auteur_ip}" class="knop" title="IP-log">IP-log</a>
				{/if}
			{/if}
		</div>
	</td>
	<td class="bericht{cycle values="0,1"}{if $post->gefilterd} filtered{/if}" id="post{$post->post_id}">
		<div class="bericht">
			{if $post->gefilterd}
				<a href="javascript:;" class="weergeeflink" onclick="jQuery('#filtered{$post->post_id}').slideDown(1000);
						jQuery(this).hide().remove()">
					&gt;&gt {$post->gefilterd}, klik om weer te geven. &lt;&lt;
				</a>
				<div id="filtered{$post->post_id}" class="verborgen">
				{/if}
				{$post->tekst|ubb}
				{if $post->bewerkt_tekst}
					<div class="bewerkt clear">
						<hr />
						{$post->bewerkt_tekst|ubb}
					</div>
				{/if}
				{if $post->gefilterd}
				</div>
			{/if}
		</div>
	</td>
</tr>