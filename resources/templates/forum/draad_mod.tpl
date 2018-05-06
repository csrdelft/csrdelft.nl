<div id="modereren">
	<table>
		<tbody>
			<tr>
				<td>
					<a href="/forum/wijzigen/{$draad->draad_id}/plakkerig" class="btn post ReloadPage" title="Verander plakkerigheid">
						{icon get="note"} maak {if $draad->plakkerig}<span class="dikgedrukt">niet</span> {/if}plakkerig
					</a>
					<br /><br />
					<a href="/forum/wijzigen/{$draad->draad_id}/eerste_post_plakkerig" class="btn post ReloadPage" title="Verander plakkerigheid van eerste post">
						<input type="checkbox" {if $draad->eerste_post_plakkerig}checked="checked"{/if}/> 1e post plakkerig
					</a>
					<br /><br />
					<a href="/forum/wijzigen/{$draad->draad_id}/pagina_per_post" class="btn post ReloadPage" title="Verander 1 pagina per post">
						<input type="checkbox" {if $draad->pagina_per_post}checked="checked"{/if}/> 1 pagina per post
					</a>
				</td>
				<td>
					<a href="/forum/wijzigen/{$draad->draad_id}/verwijderd" class="btn post confirm ReloadPage" title="Verander status verwijderd (incl. alle reacties)">
						{if $draad->verwijderd}
							{icon get="arrow_undo"} draad herstellen
						{else}
							{icon get="cross"} draad verwijderen
						{/if}
					</a>
					&nbsp;
					<a href="/forum/onderwerp/{$draad->draad_id}/prullenbak" class="btn" title="Bekijk de reacties die zijn verwijderd">{icon get="bin_closed"} verwijderde reacties</a>
					<br /><br />
					<form action="/forum/wijzigen/{$draad->draad_id}/forum_id" method="post">
						<label>Verplaats naar &nbsp;</label>
						<select name="forum_id">
							{foreach from=$categorien item=categorie}
								<optgroup label="{$categorie->titel}">
									{foreach from=$categorie->getForumDelen() item=newDeel}
										<option value="{$newDeel->forum_id}"{if $newDeel->forum_id === $draad->getForumDeel()->forum_id} selected="selected"{/if}>{$newDeel->titel}</option>
									{/foreach}
								</optgroup>
							{/foreach}
						</select>
						<input type="submit" value="Opslaan" class="btn" />
					</form>
					<br />
					<form action="/forum/wijzigen/{$draad->draad_id}/titel" method="post">
						<label>Titel aanpassen &nbsp;</label>
						<input type="text" name="titel" value="{$draad->titel}" />
						<input type="submit" value="Opslaan" class="btn" />
					</form>
					{toegang P_FORUM_BELANGRIJK}
						<br />
						<form action="/forum/wijzigen/{$draad->draad_id}/belangrijk" method="post">
							<label>Belangrijk markeren &nbsp;</label>
							<select name="belangrijk">
								<option value=""{if !$draad->belangrijk} selected="selected"{/if}>Niet belangrijk</option>
								{foreach from=CsrDelft\model\forum\ForumDradenModel::$belangrijk_opties key=group item=list}
									<optgroup label="{$group}">
										{foreach from=$list key=value item=label}
											<option value="{$value}"{if $value === $draad->belangrijk} selected="selected"{/if}>{$label}</option>
										{/foreach}
									</optgroup>
								{/foreach}
							</select>
							<input type="submit" value="Opslaan" class="btn" />
						</form>
					{/toegang}
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
