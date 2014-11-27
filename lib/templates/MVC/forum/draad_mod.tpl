<div id="modereren">
	<table>
		<tbody>
			<tr>
				<td>
					{if LoginModel::mag('P_FORUM_BELANGRIJK')}
						<a href="/forum/wijzigen/{$draad->draad_id}/belangrijk" class="btn post ReloadPage" title="Verander belangrijkheid">
							{icon get="asterisk_orange"} maak {if $draad->belangrijk}<span class="dikgedrukt">niet</span> {/if}belangrijk
						</a>
						<br /><br />
					{/if}
					<a href="/forum/wijzigen/{$draad->draad_id}/plakkerig" class="btn post ReloadPage" title="Verander plakkerigheid">
						{icon get="note"} maak {if $draad->plakkerig}<span class="dikgedrukt">niet</span> {/if}plakkerig
					</a>
					<br /><br />
					<a href="/forum/onderwerp/{$draad->draad_id}/{ForumPostsModel::instance()->getHuidigePagina()}/statistiek" class="btn" title="Bekijk statistieken gelezen door">{icon get="chart_line"} gelezen statistiek</a>
					<br /><br />
					<a href="/forum/onderwerp/{$draad->draad_id}/prullenbak" class="btn" title="Bekijk de reacties die zijn verwijderd">{icon get="bin_closed"} verwijderde reacties</a>
					<br /><br />
					<a href="/forum/wijzigen/{$draad->draad_id}/verwijderd" class="btn post confirm ReloadPage" title="Verander status verwijderd (incl. alle reacties)">
						{if $draad->verwijderd}
							{icon get="arrow_undo"} draad herstellen
						{else}
							{icon get="cross"} draad verwijderen
						{/if}
					</a>
				</td>
				<td>
					<a href="/forum/wijzigen/{$draad->draad_id}/eerste_post_plakkerig" class="btn post ReloadPage" title="Verander plakkerigheid van eerste post">
						<input type="checkbox" {if $draad->eerste_post_plakkerig}checked="checked"{/if}/> 1e post plakkerig
					</a>
					&nbsp;
					<a href="/forum/wijzigen/{$draad->draad_id}/pagina_per_post" class="btn post ReloadPage" title="Verander 1 pagina per post">
						<input type="checkbox" {if $draad->pagina_per_post}checked="checked"{/if}/> 1 pagina per post
					</a>
					<br /><br />
					<form action="/forum/wijzigen/{$draad->draad_id}/forum_id" method="post">
						<label>Verplaats naar &nbsp;</label>
						<select name="forum_id">
							{foreach from=$categorien item=cat}
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
						<label>Titel aanpassen &nbsp;</label>
						<input type="text" name="titel" value="{$draad->titel}" />
						<input type="submit" value="Opslaan" class="btn" />
					</form>
					{if $gedeeld_met_opties}
						<br />
						<form action="/forum/wijzigen/{$draad->draad_id}/gedeeld_met" method="post">
							<label>Delen met &nbsp;</label>
							<select name="gedeeld_met">
								<option value=""></option>
								{foreach from=$gedeeld_met_opties item=gedeeld_deel}
									<option value="{$gedeeld_deel->forum_id}"{if $draad->gedeeld_met === $gedeeld_deel->forum_id} selected="selected"{/if}>{$gedeeld_deel->titel}</option>
								{/foreach}
							</select>
							<input type="submit" value="Opslaan" class="btn" />
						</form>
					{/if}
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