{knopConfig prefix=/communicatie/forum/}
<form id="forum_zoeken" action="/communicatie/forum/zoeken.php" method="post"><fieldset><input type="text" name="zoeken" value="zoeken in forum" onfocus="this.value='';" /></fieldset></form>

{capture name='navlinks'}
	<div class="forumNavigatie">
		<a href="/communicatie/forum/" class="forumGrootlink">Forum</a> &raquo; 
		<a href="/communicatie/forum/categorie/{$forum->getCatID()}" class="forumGrootlink">
			{$forum->getCatTitel()|escape:'html'}
		</a><br />
		<h1>{$forum->getTitel()|escape:'html'|wordwrap:80:"\n":true}</h1>
	</div>
{/capture}
{$smarty.capture.navlinks}
{$melding}

{if $forum->isModerator()}
	<fieldset id="modereren">
		<legend>Modereren</legend>
		<div style="float: left; width: 30%;">
			{knop url="verwijder-onderwerp/`$forum->getID()`" confirm="Weet u zeker dat u dit onderwerp wilt verwijderen?" type=verwijderen text=Verwijderen class=knop} 
			<br /><br />
			{if $forum->isOpen()}
				{knop url="openheid/`$forum->getID()`" class=knop type=slotje text="sluiten (geen reactie mogelijk)"}
			{else}
				{knop url="openheid/`$forum->getID()`" class=knop type=slotje text="openen (reactie mogelijk)"}
			{/if}
			</a><br /><br />
			{if $forum->isPlakkerig()}
				{knop url="plakkerigheid/`$forum->getID()`" class=knop type=plakkerig text="verwijder plakkerigheid"}
			{else}
				{knop url="plakkerigheid/`$forum->getID()`" class=knop type=plakkerig text="maak plakkerig"}
			{/if}
				
			</a>
		</div>
		<div style="float: right; width: 60%;">
			<form action="/communicatie/forum/verplaats/{$forum->getID()}/" method="post">
				<div>Verplaats naar: <br /> 
					<select name="newCat">
						<option value="ongeldig">... selecteer</option>
						<optgroup>
						{foreach from=$forum->getCategories() item='cat'}
							{if $cat.titel=='SEPARATOR'}
						</optgroup>
						<optgroup label="------">
							{else}
								{if $cat.id!=$forum->getCatID()}<option value="{$cat.id}">{$cat.titel|escape:'html'}</option>{/if}
							{/if}
						{/foreach}
					</select> 
					<input type="submit" value="opslaan" />
				</div>
			</form>
			<form action="/communicatie/forum/onderwerp/hernoem/{$forum->getID()}/" method="post">
				<div>
					Titel aanpassen: <br />
					<input type="text" name="titel" value="{$forum->getTitel()|escape:'html'}" style="width: 250px;" />
					<input type="submit" value="opslaan" />
				</div>
			</form>
		</div>
	</fieldset>
{/if}{* einde van moderatordeel *}

<table id="forumtabel">
	<tr class="tussenschot">
		<td colspan="2"></td>
	</tr>
	{if $forum->getSoort()=='T_POLL'}
		{$peiling->view()}
	{/if}
	{foreach from=$forum->getPosts() item='bericht' name='berichten'}
		<tr>
			<td class="auteur">
				{$bericht.uid|csrnaam:'user'} schreef
				{$bericht.datum|reldate}
				<br />
				{* knopjes bij elke post *}
				{* citeerknop enkel als het onderwerp open is en als men mag posten, of als men mod is. *}
				{if $forum->magCiteren()}
					{* {knop url="reactie/`$bericht.postID`#laatste" type=citeren} *}
					<a onclick="return forumCiteren({$bericht.postID})" href="/communicatie/forum/reactie/{$bericht.postID}#laatste">
						<img src="{$csr_pics}forum/citeren.png" title="Citeer bericht" alt="Citeer bericht" style="border: 0px;" />
					</a>
				{/if}
				{* bewerken als bericht van gebruiker is, of als men mod is. *}
				{if $forum->magBewerken($bericht.postID)}
					<a onclick="forumEdit({$bericht.postID})">
						<img src="{$csr_pics}forum/bewerken.png" title="Bewerk bericht" alt="Bewerk bericht" style="border: 0px;" />
					</a>
				{/if}
				
				{if $forum->isModerator()}
					{* verwijderlinkje, niet als er maar een bericht in het onderwerp is. *}
					{knop url="verwijder-bericht/`$bericht.postID`" type=verwijderen confirm='Weet u zeker dat u dit bericht wilt verwijderen?'}
					{if $bericht.zichtbaar=='wacht_goedkeuring'}
						<br />
						{knop url="keur-goed/`$bericht.postID`" confirm='Weet u zeker dat u dit bericht wilt goedkeuren?' text='bericht goedkeuren'}
						{knop ignorePrefix=true url="/tools/stats.php?ip=`$bericht.ip`" text=ip-log}
					{elseif $bericht.zichtbaar=='spam'}
						<h1>SPAM</h1>
					{/if}
				{/if}
				
			</td>
			<td class="bericht{cycle values="0,1"}" id="post{$bericht.postID}"> 
				{$bericht.tekst|ubb}
				{if $bericht.bewerkt!=''}
					<div class="bewerkt">
						<hr />
						{$bericht.bewerkt|ubb}
					</div>
				{/if}
			</td>
		</tr>
		<tr class="tussenschot">
			<td colspan="2"></td>
		</tr>
	{/foreach} 
	{* Formulier om een bericht achter te laten *}
	<tr>
		<td class="auteur">
			<a class="forumpostlink" id="laatste">
				{if $citeerPost==0}Reageren{else}Citeren{/if}
			</a><br /><br />
			{if $forum->magToevoegen()}	
				<a onclick="vergrootTextarea('forumBericht', 10)" class="handje" title="Vergroot het invoerveld">
					Invoerveld vergroten&nbsp;&raquo;
				</a><br /><br />
				{$ubbHulp}
			{/if}			
			{* berichtje weergeven  voor moderators als het topic gesloten is. *}
			{if $forum->isModerator() AND !$forum->isOpen()}
				<br /><strong>Dit topic is gesloten, u mag reageren omdat u beheerder bent.</strong>
			{/if}
		</td>
		<td class="forumtekst">
			{if $forum->magToevoegen()} 
				<form method="post" action="/communicatie/forum/toevoegen/{$forum->getID()}">
					<p>
						{* berichtje weergeven voor niet-ingeloggede gebruikers dat ze een naam moeten vermelden. *}
						{if !$forum->isIngelogged()}
							<strong>Uw bericht wordt pas geplaatst nadat het bekeken en goedgekeurd is door de <a href="http://csrdelft.nl/actueel/groepen/Commissies/PubCie/">PubCie</a>. 
							Het vermelden van <em>uw naam en email-adres</em> is verplicht.</strong><br /><br />
						{/if}
						<div id="berichtPreviewContainer" class="previewContainer"><h3>Voorbeeld van uw bericht:</h3><div id="berichtPreview" class="preview"></div></div>
						<textarea name="bericht" id="forumBericht" class="tekst" rows="15" style="width: 100%;">{$textarea}</textarea>
						
						<input type="submit" name="submit" value="opslaan" id="forumOpslaan" />
						<input type="button" value="voorbeeld" style="color: #777;" id="forumVoorbeeld" onclick="previewPost('forumBericht', 'berichtPreview')"/>
					</p>
				</form>
			{else}
				{if $forum->isOpen()}
					U mag in dit deel van het forum niet reageren.
				{else}
					U kunt hier niet meer reageren omdat dit onderwerp gesloten is.
				{/if}
			{/if}
		</td>
	</tr>
</table>
{* linkjes voor het forum nogeens weergeven, maar alleen als het aantal berichten in het onderwerp groter is dan 4 *}
{if $forum->getSize()>4} 
	{$smarty.capture.navlinks}
{/if}

