<div id="modereren" style="display: none; border: 1px solid #999; margin: 10px; padding: 10px;">
	<table style="width: 100%;">
		<tbody>
			<tr>
				<td>
					<a href="/forum/draadwijzigen/{$draad->draad_id}/plakkerig" class="knop post" title="Verander plakkerigheid">
						{icon get="note"} maak {if $draad->plakkerig}<strong>niet</strong> {/if}plakkerig
					</a>
					<br /><br />
					<a href="/forum/draadwijzigen/{$draad->draad_id}/belangrijk" class="knop post" title="Verander belangrijkheid">
						{icon get="tag_blue"} maak {if $draad->belangrijk}<strong>niet</strong> {/if}belangrijk
					</a>
					<br /><br />
					<a href="/forum/draadwijzigen/{$draad->draad_id}/verwijderd" class="knop post confirm" title="Verwijder forumdraad">
						{icon get="cross"} Verwijderen
					</a>
				</td>
				<td>
					<label for="newCat">Verplaats naar:</label>
					<select name="newCat" onchange="location.href = '/forum/draadverplaatsen/{$draad->draad_id}/' + this.value;">
						{foreach from=ForumModel::instance()->getForum() item=cat}
							<optgroup label="{$cat->titel}">
								{foreach from=$cat->getForumDelen() item=newDeel}
									<option value="{$newDeel->forum_id}"{if $newDeel->forum_id === $deel->forum_id} selected="selected"{/if}>{$newDeel->titel}</option>
								{/foreach}
							</optgroup>
						{/foreach}
					</select>
					<br /><br />
					<form action="/forum/draadwijzigen/{$draad->draad_id}/titel" method="post">
						<label for="titel">Titel aanpassen:</label>
						<input type="text" name="titel" value="{$draad->titel}" style="width: 250px;" />
						<input type="submit" value="opslaan" />
					</form>
				</td>
				<td>
					<div style="width: 25px; cursor: pointer; padding: 5px;" onclick="$('#btn_mod').toggle();$('#modereren').slideUp();">X</div>
				</td>
			</tr>
		</tbody>
	</table>
</div>