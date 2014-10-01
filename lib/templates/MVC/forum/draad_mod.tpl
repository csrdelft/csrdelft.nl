<div id="modereren">
	<table>
		<tbody>
			<tr>
				<td>
					<a href="/forum/wijzigen/{$draad->draad_id}/plakkerig" class="knop post ReloadPage" title="Verander plakkerigheid">
						{icon get="note"} maak {if $draad->plakkerig}<strong>niet</strong> {/if}plakkerig
					</a>
					<br /><br />
					{if LoginModel::mag('P_FORUM_BELANGRIJK')}
						<a href="/forum/wijzigen/{$draad->draad_id}/belangrijk" class="knop post ReloadPage" title="Verander belangrijkheid">
							{icon get="asterisk_orange"} maak {if $draad->belangrijk}<strong>niet</strong> {/if}belangrijk
						</a>
					{/if}
					<br /><br />
					<a href="/forum/wijzigen/{$draad->draad_id}/verwijderd" class="knop post confirm ReloadPage" title="Verander status verwijderd (incl. alle reacties)">
						{if $draad->verwijderd}
							{icon get="arrow_undo"} draad herstellen
						{else}
							{icon get="cross"} draad verwijderen
						{/if}
					</a>
				</td>
				<td>
					<a href="/forum/wijzigen/{$draad->draad_id}/eerste_post_plakkerig" class="knop post ReloadPage" title="Verander plakkerigheid van eerste post">
						{icon get="note"} maak eerste post {if $draad->eerste_post_plakkerig}<strong>niet</strong> {/if}plakkerig
					</a>
					<br /><br />
					<form action="/forum/wijzigen/{$draad->draad_id}/forum_id" method="post">
						<label for="forum_id">Verplaats naar &nbsp;</label>
						<select name="forum_id">
							{foreach from=ForumModel::instance()->getForum() item=cat}
								<optgroup label="{$cat->titel}">
									{foreach from=$cat->getForumDelen() item=newDeel}
										<option value="{$newDeel->forum_id}"{if $newDeel->forum_id === $deel->forum_id} selected="selected"{/if}>{$newDeel->titel}</option>
									{/foreach}
								</optgroup>
							{/foreach}
						</select>
						<input type="submit" value="Opslaan" />
					</form>
					<br />
					<form action="/forum/wijzigen/{$draad->draad_id}/titel" method="post">
						<label for="titel">Titel aanpassen &nbsp;</label>
						<input type="text" name="titel" value="{$draad->titel}" />
						<input type="submit" value="Opslaan" />
					</form>
				</td>
				<td>
					<span id="modsluiten" onclick="$('#togglemodknop').toggle();
							$('#modereren').slideUp();
							$('#forumtabel a.forummodknop').fadeOut();" title="Moderatie-functies verbergen">×</span>
				</td>
			</tr>
		</tbody>
	</table>
</div>