{knopConfig prefix=/communicatie/forum/}
<form id="forum_zoeken" action="/communicatie/forum/zoeken.php" method="post"><fieldset><input type="text" name="zoeken" value="zoeken in forum" onfocus="this.value='';" /></fieldset></form>

{capture name='navlinks'}
	<div class="forumNavigatie">
		<a href="/communicatie/forum/" class="forumGrootlink">Forum</a> &raquo; 
		<a href="/communicatie/forum/categorie/{$onderwerp->getCategorieID()}" class="forumGrootlink">
			{$onderwerp->getCategorie()->getNaam()|escape:'html'}
		</a><br />
		<h1>{$onderwerp->getTitel()|escape:'html'|wordwrap:80:"\n":true}</h1>
	</div>
{/capture}
{$smarty.capture.navlinks}
{$melding}

{if $onderwerp->isModerator()}
	<fieldset id="modereren">
		<legend>Modereren</legend>
		<div style="float: left; width: 30%;">
			{knop url="verwijder-onderwerp/`$onderwerp->getID()`" confirm="Weet u zeker dat u dit onderwerp wilt verwijderen?" type=verwijderen text=Verwijderen class=knop} 
			<br /><br />
			{if $onderwerp->isOpen()}
				{knop url="openheid/`$onderwerp->getID()`" class=knop type=slotje text="sluiten (geen reactie mogelijk)"}
			{else}
				{knop url="openheid/`$onderwerp->getID()`" class=knop type=slotje text="openen (reactie mogelijk)"}
			{/if}
			<br /><br />
			{if $onderwerp->isPlakkerig()}
				{knop url="plakkerigheid/`$onderwerp->getID()`" class=knop type=plakkerig text="verwijder plakkerigheid"}
			{else}
				{knop url="plakkerigheid/`$onderwerp->getID()`" class=knop type=plakkerig text="maak plakkerig"}
			{/if}	
		</div>
		<div style="float: right; width: 60%;">
			<form action="/communicatie/forum/verplaats/{$onderwerp->getID()}/" method="post">
				<div>Verplaats naar: <br /> 
					<select name="newCat">
						<option value="ongeldig">... selecteer</option>
						<optgroup>
						{foreach from=$onderwerp->getCategorie()->getAll() item='cat'}
							{if $cat.titel=='SEPARATOR'}
						</optgroup>
						<optgroup label="------">
							{else}
								{if $cat.id!=$onderwerp->getCategorieID()}<option value="{$cat.id}">{$cat.titel|escape:'html'}</option>{/if}
							{/if}
						{/foreach}
					</select> 
					<input type="submit" value="opslaan" />
				</div>
			</form>
			<form action="/communicatie/forum/onderwerp/hernoem/{$onderwerp->getID()}/" method="post">
				<div>
					Titel aanpassen: <br />
					<input type="text" name="titel" value="{$onderwerp->getTitel()|escape:'html'}" style="width: 250px;" />
					<input type="submit" value="opslaan" />
				</div>
			</form>
		</div>
	</fieldset>
{/if}{* einde van moderatordeel *}

<table id="forumtabel">
	{if $onderwerp->getPaginaCount()>1}
		<tr class="tussenschot">
			<td colspan="2"></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>
				<div class="forum_paginering">
					Pagina: {sliding_pager baseurl="/communicatie/forum/onderwerp/`$onderwerp->getID()`/" 
						pagecount=$onderwerp->getPaginaCount() curpage=$onderwerp->getPagina()
						txt_prev="&lt;" separator="" txt_next="&gt;" show_always=true show_first_last=false show_prev_next=false}
				</div>
			</td>
		</tr>
	{/if}
	
	<tr class="tussenschot">
		<td colspan="2"></td>
	</tr>

	{foreach from=$onderwerp->getPosts() item='bericht' name='berichten'}
		<tr>
			<td class="auteur">
				<span class="togglePasfoto" id="t{$bericht.uid}-{$bericht.id}" title="Toon pasfoto van dit lid">&raquo;</span>&nbsp;<a href="/communicatie/forum/reactie/{$bericht.id}" class="postlink" title="Link naar deze post">&rarr;</a>
				{$bericht.uid|csrnaam:'user'}<br />
				<span class="moment">{$bericht.datum|reldate}</span>
				<br />
				<div id="p{$bericht.id}" class="forumpasfoto{if $loginlid->instelling('forum_toonpasfotos')=='nee'}verborgen{/if}" >
					{if $loginlid->instelling('forum_toonpasfotos')=='ja'}
						{$bericht.uid|csrnaam:'pasfoto'}
					{/if}
				</div>
				{* knopjes bij elke post *}
				{* citeerknop enkel als het onderwerp open is en als men mag posten, of als men mod is. *}
				{if $onderwerp->magCiteren()}
					{* {knop url="reactie/`$bericht.id`#laatste" type=citeren} *}
					<a onclick="return forumCiteren({$bericht.id})" href="/communicatie/forum/reactie/{$bericht.id}#laatste">
						<img src="{$csr_pics}forum/citeren.png" title="Citeer bericht" alt="Citeer bericht" style="border: 0px;" />
					</a>
				{/if}
				{* bewerken als bericht van gebruiker is, of als men mod is. *}
				{if $onderwerp->magBewerken($bericht.id)}
					<a onclick="forumBewerken({$bericht.id})">
						<img src="{$csr_pics}forum/bewerken.png" title="Bewerk bericht" alt="Bewerk bericht" style="border: 0px;" />
					</a>
				{/if}
				
				{if $onderwerp->isModerator()}
					{* verwijderlinkje, niet als er maar een bericht in het onderwerp is. *}
					{knop url="verwijder-bericht/`$bericht.id`" type=verwijderen confirm='Weet u zeker dat u dit bericht wilt verwijderen?'}
					{if $bericht.zichtbaar=='wacht_goedkeuring'}
						<br />
						{knop url="keur-goed/`$bericht.id`" confirm='Weet u zeker dat u dit bericht wilt goedkeuren?' text='bericht goedkeuren'}
						{knop ignorePrefix=true url="/tools/stats.php?ip=`$bericht.ip`" text=ip-log}
					{elseif $bericht.zichtbaar=='spam'}
						<h1>SPAM</h1>
					{/if}
				{/if}
				
			</td>
			<td class="bericht{cycle values="0,1"}" id="post{$bericht.id}"> 
				{$bericht.tekst|ubb}
				{if $bericht.bewerkt!=''}
					<div class="bewerkt clear">
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
	
	{if $onderwerp->getPaginaCount()>1}
		<tr>
			<td>&nbsp;</td>
			<td>
				<div class="forum_paginering">
					Pagina: {sliding_pager baseurl="/communicatie/forum/onderwerp/`$onderwerp->getID()`/" 
						pagecount=$onderwerp->getPaginaCount() curpage=$onderwerp->getPagina()
						txt_prev="&lt;" separator="" txt_next="&gt;" show_always=true show_first_last=false show_prev_next=false}
				</div>
			</td>
		</tr>
		<tr class="tussenschot">
			<td colspan="2"></td>
		</tr>
	{/if}

	{* Formulier om een bericht achter te laten *}
	<tr>
		<td class="auteur">
			<a class="forumpostlink" id="laatste">Reageren</a><br /><br />
			{* berichtje weergeven  voor moderators als het topic gesloten is. *}
			{if $onderwerp->isModerator() AND !$onderwerp->isOpen()}
				<br /><strong>Dit topic is gesloten, u kunt als moderator <a href="#forumReageren" onclick="toggleDiv('forumReageren')">toch&nbsp;reageren</a>.</strong>
			{/if}
		</td>
		<td class="forumtekst">
			{if $onderwerp->magToevoegen()} 
				<form method="post" action="/communicatie/forum/toevoegen/{$onderwerp->getID()}" id="forumReageren" {if !$onderwerp->isOpen()}class="gesloten"{/if}>
					<fieldset>
						{* berichtje weergeven voor niet-ingeloggede gebruikers dat ze een naam moeten vermelden. *}
						{if $onderwerp->needsModeration()}
							<strong>Uw bericht wordt pas geplaatst nadat het bekeken en goedgekeurd is door de <a href="http://csrdelft.nl/actueel/groepen/Commissies/PubCie/">PubCie</a>. 
							Het vermelden van <em>uw naam en email-adres</em> is verplicht.</strong><br /><br />
							<label for="email">Email-adres:</label><input type="text" name="email" /><br />
						{/if}
						<div id="berichtPreviewContainer" class="previewContainer"><div id="berichtPreview" class="preview"></div></div>
						<textarea name="bericht" id="forumBericht" class="forumBericht" rows="12">{$textarea}</textarea>
						
						<a style="float: right;" class="handje knop" onclick="toggleDiv('ubbhulpverhaal')" title="Opmaakhulp weergeven">UBB</a>
						<a style="float: right;" class="handje knop" onclick="vergrootTextarea('forumBericht', 10)" title="Vergroot het invoerveld"><strong>&uarr;&darr;</strong></a>
						<input type="submit" name="submit" value="opslaan" id="forumOpslaan" />
						<input type="button" value="voorbeeld" style="color: #777;" id="forumVoorbeeld" onclick="previewPost('forumBericht', 'berichtPreview')"/>
					</fieldset>
				</form>
			{else}
				{if $onderwerp->isOpen()}
					U mag in dit deel van het forum niet reageren.
				{else}
					U kunt hier niet meer reageren omdat dit onderwerp gesloten is.
				{/if}
			{/if}
		</td>
	</tr>
</table>
{* linkjes voor het forum nogeens weergeven, maar alleen als het aantal berichten in het onderwerp groter is dan 4 *}
{if $onderwerp->getSize()>4} 
	{$smarty.capture.navlinks}
{/if}
{if $loginlid->hasPermission('P_LEDEN_READ')}
	{literal}
	<script>
	$(document).ready(function() {
		//alle pijltes voor de namen goedzetten, en visueel 'klikbaar' maken
		$('.togglePasfoto').each( function(){
			knop=$(this).addClass('handje');

			if($('#p'+knop.attr('id').substr(1).split('-')[1]).html().trim()==''){
				knop.html("»"); 
			}else{
				knop.html('v');
			}
		});
		$('.togglePasfoto').click(function(){
			knop=$(this);
			var parts=knop.attr('id').substr(1).split('-');
			var pasfoto=$('#p'+parts[1]);

			if(pasfoto.html().trim()==''){
				pasfoto.html('<img src="/tools/pasfoto/'+parts[0]+'.png" width="50" />');
			}
			if(pasfoto.hasClass('verborgen')){
				knop.html('v');
			}else{
				//om een of andere reden snapt chromium het niet als ik hier een htmlentitie in stop ipv het karakter zelf
				knop.html("»");
			}
			pasfoto.toggleClass('verborgen');
		});
	});
	</script>
	{/literal}
{/if}
