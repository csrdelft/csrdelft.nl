<form id="forum_zoeken" action="/communicatie/forum/zoeken.php" method="post"><fieldset><input type="text" name="zoeken" value="zoeken in forum" onfocus="this.value = '';" /></fieldset></form>

{capture name='navlinks'}
	<div class="forumNavigatie">
		<a href="/forum/" class="forumGrootlink">Forum</a> » {$categorie->titel}
		<h1>{$deel->titel}</h1>
	</div>
{/capture}
{$smarty.capture.navlinks}
{$view->getMelding()}

<table id="forumtabel">
	<thead>
		<tr>
			<th>Titel</th>
			<th>Reacties</th>
			<th>Auteur</th>
			<th>Recent</th>
		</tr>
	</thead>
	<tbody>
		{if !$deel->hasForumDraden()}
			<tr>
				<td colspan="4">Dit forum is nog leeg.</td>
			</tr>
		{/if}
		{foreach from=$deel->getForumDraden() item=draad}
			<tr class="forumdraad kleur{cycle values="0,1"}">
				<td class="titel">
					{if $draad->wacht_goedkeuring}
						[ter goedkeuring...]
					{/if}
					<a href="/forumdraad/{$draad->draad_id}"{if $draad->alGelezen()} class="updatedTopic"{/if}>
						{if $draad->isPlakkerig()}
							<img src="{icon get="plakkerig" notag=true}" title="Dit onderwerp is plakkerig, het blijft bovenaan." alt="plakkerig" />&nbsp;&nbsp;
						{/if}
						{if !$draad->isOpen()}
							<img src="{icon get="slotje" notag=true}" title="Dit onderwerp is gesloten, u kunt niet meer reageren" alt="sluiten" />&nbsp;&nbsp;
						{/if}
						{$draad->getTitel()|wordwrap:60:"\n":true|escape:'html'}
					</a>
					{sliding_pager baseurl="/communicatie/forum/onderwerp/`$draad->getID()`/"
						pagecount=$draad->getPaginaCount() curpage=$draad->getPagina()
						link_current=true txt_pre="[ " txt_prev="&lt;" separator=" " txt_next="&gt;" txt_post=" ]" show_first_last=false show_prev_next=false}
				</td>
				<td class="reacties">{$draad->getReacties()}</td>
				<td class="reacties">{$draad->getUid()|csrnaam:'user'}</td>
				<td class="reactiemoment">
					{if $loginlid->getInstelling('forum_datumWeergave') === 'relatief'}
						{$draad->getLastpost()|reldate}
					{else}
						{$draad->getLastpost()}
					{/if}
					<br />
					<a href="/communicatie/forum/reactie/{$draad->getLastpostID()}">bericht</a> door
					{$draad->getLastuser()|csrnaam:'user'}
				</td>
			</tr>
		{/foreach}
	</tbody>
	<tr>
		<th colspan="2">&nbsp;</th>
		<th colspan="2">
			{sliding_pager baseurl="/communicatie/forum/categorie/`$categorie->getID()`/"
				pagecount=$categorie->getPaginaCount() curpage=$categorie->getPagina()
				txt_first="&laquo;" txt_prev="&lt;" separator=" " txt_next="&gt;" txt_last="&raquo;"}
		</th>
	</tr>

	{if $categorie->magPosten()}
		<tr>
			<td colspan="4" class="forumtekst">
				<form method="post" action="/communicatie/forum/onderwerp-toevoegen/{$categorie->getID()}" id="forumForm">
					{if $loginlid->hasPermission('P_LOGGED_IN')}
						{if $categorie->isOpenbaar()} 
							<strong>Openbaar forum:</strong> Iedereen mag dit lezen en zoekmachines nemen het op in hun zoekresultaten.<br /><br />
						{/if}
						Hier kunt u een onderwerp toevoegen in deze categorie van het forum. Kijkt u vooraf goed of het
						onderwerp waarover u post hier wel thuishoort.<br /><br />
					{else}
						{*	melding voor niet ingelogde gebruikers die toch willen posten. Ze worden 'gemodereerd', dat
						wil zeggen, de topics zijn nog niet direct zichtbaar. *}
						Hier kunt u een bericht toevoegen aan het forum. Het zal echter niet direct zichtbaar worden, maar
						&eacute;&eacute;rst door de PubCie worden goedgekeurd. Zoekmachines nemen berichten van dit openbare 
						forumdeel op in hun zoekresultaten.<br />
						<span style="text-decoration: underline;">Het is hierbij verplicht om uw naam in het bericht te plaatsen.</span><br /><br />
						<label for="email">Email-adres:</label><input type="text" id="email" name="email" /><br /><br />
						{* spam trap, must be kept empty! *}
						<input type="text" name="firstname" value="" class="verborgen" />
					{/if}
					<label for="titel"><a class="forumpostlink" style="color: #4D4D4D; text-decoration: none;" name="laatste">Titel</a></label>
					<input type="text" name="titel" id="titel" value="" class="tekst" style="width: 578px;" tabindex="1" /><br /><br />
					<label for="forumBericht">Bericht</label><div id="textareaContainer">
						<div id="berichtPreviewContainer" class="previewContainer"><div id="berichtPreview" class="preview"></div></div>
						<textarea name="bericht" id="forumBericht" rows="10" cols="80" class="forumBericht" tabindex="2"></textarea>
					</div>
                    <div class="butn">
                        <label>&nbsp;</label>
                        <a style="float: right; margin-right:0" class="handje knop" onclick="$('#ubbhulpverhaal').toggle();" title="Opmaakhulp weergeven">Opmaak</a>
                        <a style="float: right;" class="handje knop" onclick="vergrootTextarea('forumBericht', 10)" title="Vergroot het invoerveld"><strong>&uarr;&darr;</strong></a>
                        <input type="submit" name="submit" value="opslaan" />
                        <input type="button" value="voorbeeld" id="forumVoorbeeld" onclick="previewPost('forumBericht', 'berichtPreview')"/>
                    </div>
				</form>
			</td>
		</tr>
	{/if}
</table>
{$smarty.capture.navlinks}
