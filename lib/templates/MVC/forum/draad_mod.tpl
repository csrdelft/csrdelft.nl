<div id="modereren" style="display: none; border: 1px solid #999; margin: 10px; padding: 10px;">
	<table style="width: 100%;">
		<tbody>
			<tr>
				<td>
					<a href="/forum/wijzigen/{$draad->draad_id}/plakkerig" class="knop" title="Verander plakkerigheid">
						{icon get="note"} maak {if $draad->plakkerig}<strong>niet</strong> {/if}plakkerig
					</a>
					<br /><br />
					<a href="/forum/wijzigen/{$draad->draad_id}/belangrijk" class="knop" title="Verander belangrijkheid">
						{icon get="asterisk_orange"} maak {if $draad->belangrijk}<strong>niet</strong> {/if}belangrijk
					</a>
					<br /><br />
					<a href="/forum/wijzigen/{$draad->draad_id}/verwijderd" class="knop" title="Verander is verwijderd" onclick="return confirm('Weet u zeker dat u dit gehele forumdraad met alle reacties wilt {if $draad->verwijderd}herstellen{else}verwijderen{/if}?');">
						{if $draad->verwijderd}
							{icon get="arrow_undo"} draad herstellen
						{else}
							{icon get="cross"} draad verwijderen
						{/if}
					</a>
				</td>
				<td>
					<a href="/forum/wijzigen/{$draad->draad_id}/eerste_post_plakkerig" class="knop" title="Verander plakkerigheid van eerste post">
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
						<input type="text" name="titel" value="{$draad->titel}" style="width: 300px;" />
						<input type="submit" value="Opslaan" />
					</form>
				</td>
				<td style="width: 25px;">
					<span id="modsluiten" onclick="$('#togglemodknop').toggle();
							$('#modereren').slideUp();
							$('#forumtabel a.forummodknop').fadeOut();" title="Moderatie-functies verbergen">×</span>
				</td>
			</tr>
		</tbody>
	</table>
</div>