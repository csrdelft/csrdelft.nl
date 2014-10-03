<div id="modereren">
	<table>
		<tbody>
			<tr>
				<td>
					{if LoginModel::mag('P_FORUM_BELANGRIJK')}
						<a href="/forum/wijzigen/{$draad->draad_id}/belangrijk" class="knop post ReloadPage" title="Verander belangrijkheid">
							{icon get="asterisk_orange"} maak {if $draad->belangrijk}<span class="dikgedrukt">niet</span> {/if}belangrijk
						</a>
						<br /><br />
					{/if}
					<a href="/forum/wijzigen/{$draad->draad_id}/plakkerig" class="knop post ReloadPage" title="Verander plakkerigheid">
						{icon get="note"} maak {if $draad->plakkerig}<span class="dikgedrukt">niet</span> {/if}plakkerig
					</a>
					<br /><br />
					<a href="/forum/wijzigen/{$draad->draad_id}/eerste_post_plakkerig" class="knop post ReloadPage" title="Verander plakkerigheid van eerste post">
						{icon get="note"} maak eerste post {if $draad->eerste_post_plakkerig}<span class="dikgedrukt">niet</span> {/if}plakkerig
					</a>
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
					<form action="/forum/wijzigen/{$draad->draad_id}/gedeeld_met" method="post">
						<label>Delen met &nbsp;</label>
						<select name="gedeeld_met">
							<option value=""{if empty($draad->gedeeld_met)} selected="selected"{/if}></option>
							{assign var=found value=false}
							{if startsWith($deel->rechten_posten, 'verticale:')}
								<optgroup label="Verticalen">
									{foreach from=VerticalenModel::instance()->find() item=verticale}
										{if $verticale->id > 0}
											{assign var=filter value="verticale:"|cat:$verticale->naam}
											<option value="{$filter}"{if $filter === $draad->gedeeld_met}{assign var=found value=true} selected="selected"{/if}>{$verticale->naam}</option>
										{/if}
									{/foreach}
								</optgroup>
							{elseif startsWith($deel->rechten_posten, 'lidjaar:')}
								<optgroup label="Lichting">
									{assign var=start value=Lichting::getJongsteLichting()}
									{assign var=end value=$start-7}
									{for $lidjaar=$start to $end step -1}
										{assign var=filter value="lidjaar:"|cat:$lidjaar}
										<option value="{$filter}"{if $filter === $draad->gedeeld_met}{assign var=found value=true} selected="selected"{/if}>{$lidjaar}</option>
									{/for}
								</optgroup>
							{/if}
							{if !$found and !empty($draad->gedeeld_met)}
								<option value="{$draad->gedeeld_met}" selected="selected">{$draad->gedeeld_met}</option>
							{/if}
						</select>
						<input type="submit" value="Opslaan" />
					</form>
					<br />
					<form action="/forum/wijzigen/{$draad->draad_id}/forum_id" method="post">
						<label>Verplaats naar &nbsp;</label>
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
						<label>Titel aanpassen &nbsp;</label>
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