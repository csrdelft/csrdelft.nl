<form id="forum_zoeken" action="/communicatie/forum/zoeken.php" method="post"><fieldset><input type="text" name="zoeken" value="zoeken in forum" onfocus="this.value='';" /></fieldset></form>

{capture name='navlinks'}
	<div class="forumNavigatie">
		<a href="/communicatie/forum/" class="forumGrootlink">Forum</a>
		<h1>{$categorie->getNaam()}</h1>
	</div>
{/capture}
{$smarty.capture.navlinks}
{$melding}

<table id="forumtabel">
	<tr>
		<th>Titel</th>
		<th>Reacties</th>
		<th>Auteur</th>
		<th>verandering</th>
	</tr>
	{if !is_array($categorie->getOnderwerpen())}
		<tr>
			<td colspan="3">Deze categorie bevat nog geen berichten of deze categorie bestaat niet.</td>
		</tr>
	{else}
		{foreach from=$categorie->getOnderwerpen() item=onderwerp}
			<tr class="kleur{cycle values="0,1"}">
				<td class="titel">
					{if $onderwerp->getZichtbaarheid()=='wacht_goedkeuring'}[ter goedkeuring...]{/if}
					<a href="/communicatie/forum/onderwerp/{$onderwerp->getID()}"{if $onderwerp->isUpdated()} class="updatedTopic"{/if}>
						{if $onderwerp->isPlakkerig()}
							<img src="{icon get="plakkerig" notag=true}" title="Dit onderwerp is plakkerig, het blijft bovenaan." alt="plakkerig" />&nbsp;&nbsp;
						{/if}
						{if !$onderwerp->isOpen()}
							<img src="{icon get="slotje" notag=true}" title="Dit onderwerp is gesloten, u kunt niet meer reageren" alt="sluiten" />&nbsp;&nbsp;
						{/if}
						{$onderwerp->getTitel()|wordwrap:60:"\n":true|escape:'html'}
					</a>
						{sliding_pager baseurl="/communicatie/forum/onderwerp/`$onderwerp->getID()`/"
						pagecount=$onderwerp->getPaginaCount() curpage=$onderwerp->getPagina()
						link_current=true txt_pre="[ " txt_prev="&lt;" separator=" " txt_next="&gt;" txt_post=" ]" show_first_last=false show_prev_next=false}
				</td>
				<td class="reacties">{$onderwerp->getReacties()}</td>
				<td class="reacties">{$onderwerp->getUid()|csrnaam:'user'}</td>
				<td class="reactiemoment">
					{$onderwerp->getLastpost()|reldate}<br />
					<a href="/communicatie/forum/reactie/{$onderwerp->getLastpostID()}">bericht</a> door
					{$onderwerp->getLastuser()|csrnaam:'user'}
				</td>
			</tr>
		{/foreach}
	{/if}
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
						{ if $categorie->isOpenbaar()} 
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
						<label for="email">Email-adres:</label><input type="text" name="email" /><br /><br />
						{* spam trap, must be kept empty! *}
						<input type="text" name="firstname" value="" class="verborgen" />
					{/if}
					<label><a class="forumpostlink" name="laatste">Titel</a></label>
						<input type="text" name="titel" value="" class="tekst" style="width: 578px;" tabindex="1" /><br /><br />
					<label for="bericht">Bericht</label><div id="textareaContainer">
						<div id="berichtPreviewContainer" class="previewContainer"><div id="berichtPreview" class="preview"></div></div>
						<textarea name="bericht" id="forumBericht" rows="10" cols="80" class="forumBericht" tabindex="2"></textarea>
					</div>
					<label>&nbsp;</label>
					<a style="float: right;" class="handje knop" onclick="toggleDiv('ubbhulpverhaal')" title="Opmaakhulp weergeven">UBB</a>
					<a style="float: right;" class="handje knop" onclick="vergrootTextarea('forumBericht', 10)" title="Vergroot het invoerveld"><strong>&uarr;&darr;</strong></a>
					<input type="submit" name="submit" value="verzenden" /> <input type="button" value="voorbeeld" style="color: #777;" id="forumVoorbeeld" onclick="previewPost('forumBericht', 'berichtPreview')"/>
				</form>
			</td>
		</tr>
	{/if}
</table>
{$smarty.capture.navlinks}
