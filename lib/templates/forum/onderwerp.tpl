{knopConfig prefix=/communicatie/forum/}

<form id="forum_zoeken" action="/communicatie/forum/zoeken.php" method="post">
	<input type="text" name="zoeken" value="zoeken in forum" onfocus="this.value='';" />
{if $onderwerp->isModerator()}
	<div id="btn_mod">
		<a class="knop"onclick="$('#modereren').slideDown();$('#btn_mod').toggle();">{icon get="bullet_wrench"} Modereren&nbsp;</a>
	</div>
{/if}
</form>

{capture name='navlinks'}
	<div class="forumNavigatie">
		<a href="/communicatie/forum/" class="forumGrootlink">Forum</a> &raquo;
		<a href="/communicatie/forum/categorie/{$onderwerp->getCategorieID()}" class="forumGrootlink">
			{$onderwerp->getCategorie()->getNaam()|escape:'html'}
		</a><br />
		{if $onderwerp->isModerator()}
		<div style="display:inline-block;margin-right:3px;">
		{if $onderwerp->isOpen()}
			<a href="/communicatie/forum/openheid/{$onderwerp->getID()}" class="knop" title="Sluiten (geen reactie mogelijk)"
			   onmouseover="$(this).children('img').attr('src', 'http://plaetjes.csrdelft.nl/famfamfam/lock.png');"
			   onmouseout="$(this).children('img').attr('src', 'http://plaetjes.csrdelft.nl/famfamfam/lock_open.png');"
			   >{icon get="lock_open"}</a>
		{else}
			<a href="/communicatie/forum/openheid/{$onderwerp->getID()}" class="knop" title="Openen (reactie mogelijk)"
			   onmouseover="$(this).children('img').attr('src', 'http://plaetjes.csrdelft.nl/famfamfam/lock_break.png');"
			   onmouseout="$(this).children('img').attr('src', 'http://plaetjes.csrdelft.nl/famfamfam/lock.png');"
			   >{icon get="lock"}</a>
		{/if}
		</div>
		{/if}
		<h1 style="display:inline-block;">{$onderwerp->getTitel()|escape:'html'|wordwrap:80:"\n":true}</h1><br />
	</div>
{/capture}
{$smarty.capture.navlinks}
{$melding}

{if $onderwerp->isModerator()}
	<div id="modereren" style="display:none;border:1px solid #999;margin:10px;padding:10px;">
		<table style="width:100%;"><tr>
		<td>
			{if $onderwerp->isPlakkerig()}
				{knop url="plakkerigheid/`$onderwerp->getID()`" class=knop type=plakkerig text="verwijder plakkerigheid"}
			{else}
				{knop url="plakkerigheid/`$onderwerp->getID()`" class=knop type=plakkerig text="maak plakkerig"}
			{/if}
			<br /><br />
			{if $onderwerp->isBelangrijk()}
				{knop url="belangrijk/`$onderwerp->getID()`" class=knop type=plakkerig text="maak niet belangrijk"}
			{else}
				{knop url="belangrijk/`$onderwerp->getID()`" class=knop type=plakkerig text="maak belangrijk"}
			{/if}
			<br /><br />
			{knop url="verwijder-onderwerp/`$onderwerp->getID()`" confirm="Weet u zeker dat u dit onderwerp wilt verwijderen?" type=verwijderen text=Verwijderen class=knop}
		</td>
		<td>
			<form action="/communicatie/forum/verplaats/{$onderwerp->getID()}/" method="post">
				<div>Verplaats naar: <br />
					<select name="newCat">
						<optgroup label="selecteer...">
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
			<br />
			<form action="/communicatie/forum/onderwerp/hernoem/{$onderwerp->getID()}/" method="post">
				<div>Titel aanpassen: <br />
					<input type="text" name="titel" value="{$onderwerp->getTitel()|escape:'html'}" style="width: 250px;" />
					<input type="submit" value="opslaan" />
				</div>
			</form>
		</td>
		<td style="width:25px;cursor:pointer;padding:5px;" onclick="$('#btn_mod').toggle();$('#modereren').slideUp();">X</div>
		</td>
		</tr></table>
	</div>
{/if}{* einde van moderatordeel *}

{capture name='magreageren'}{strip}
	{if !$onderwerp->magToevoegen()}
		<div style="font-style:italic;padding:5px;float:left;">
		{if !$onderwerp->isOpen()}
			U kunt hier niet meer reageren omdat dit onderwerp gesloten is.
		{else}
			U mag in dit deel van het forum niet reageren.
		{/if}
		</div>
	{/if}
{/strip}{/capture}

<table id="forumtabel">
	{if $onderwerp->getPaginaCount()>1}
		<tr class="tussenschot">
			<td colspan="2"></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>{$smarty.capture.magreageren}
				<div class="forum_paginering">
					Pagina: {sliding_pager baseurl="/communicatie/forum/onderwerp/`$onderwerp->getID()`/"
						pagecount=$onderwerp->getPaginaCount() curpage=$onderwerp->getPagina()
						txt_prev="&lt;" separator="" txt_next="&gt;" show_always=true show_first_last=false show_prev_next=false}
				</div>
			</td>
		</tr>
	{elseif $smarty.capture.magreageren!==''}
		<tr>
			<td>&nbsp;</td>
			<td class="forumtekst">{$smarty.capture.magreageren}</td>
		</tr>
	{/if}

	<tr class="tussenschot">
		<td colspan="2"></td>
	</tr>

	{foreach from=$onderwerp->getPosts() item='bericht' name='berichten'}
		<tr>
			<td class="auteur">

				<a href="/communicatie/forum/reactie/{$bericht.id}" class="postlink" title="Link naar deze post">&rarr;</a>
				{$bericht.uid|csrnaam:'user'}
			{if $loginlid->hasPermission('P_LEDEN_READ')}
				<span tabindex="0"  id="t{$bericht.uid}-{$bericht.id}" class="togglePasfoto"
				{if $loginlid->instelling('forum_toonpasfotos')=='nee'} title="Toon pasfoto">&raquo;{else}>{/if}</span>
			{/if}<br />
				<div id="p{$bericht.id}" class="forumpasfoto{if $loginlid->instelling('forum_toonpasfotos')=='nee'} verborgen">{else}">{$bericht.uid|csrnaam:'pasfoto'}{/if}</div>
				<span class="moment"><abbr class="timeago" title="{$bericht.utc}">{$bericht.datum|reldate}</abbr></span><br />

				{* knopjes bij elke post *}
				{* citeerknop enkel als het onderwerp open is en als men mag posten, of als men mod is. *}
				{if $onderwerp->magCiteren()}
					{* {knop url="reactie/`$bericht.id`#laatste" type=citeren} *}
					<a onclick="return forumCiteren({$bericht.id})" href="/communicatie/forum/reactie/{$bericht.id}#laatste">
						<img src="{icon get="citeren" notag=true}" title="Citeer bericht" alt="Citeer bericht" style="border: 0px;" />
					</a>
				{/if}
				{* bewerken als bericht van gebruiker is, of als men mod is. *}
				{if $onderwerp->magBewerken($bericht.id)}
					<a href="javascript:;" onclick="forumBewerken({$bericht.id})">
						<img src="{icon get="bewerken" notag=true}" title="Bewerk bericht" alt="Bewerk bericht" style="border: 0px;" />
					</a>
				{/if}

				{if $onderwerp->isModerator()}{* verwijderlinkje emoticon_unhappy sound_delete*}
					{knop url="markeer-bericht-offtopic/`$bericht.id`" type=offtopic confirm='Weet u zeker dat u dit bericht offtopic wil markeren?'}
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
			<td class="bericht{cycle values="0,1"}{if $bericht.filtered} filtered{/if}" id="post{$bericht.id}">
				<div class="bericht">
				{if $bericht.filtered}
					<a href="javascript:;" class="weergeeflink" onclick="jQuery('#filtered{$bericht.id}').slideDown(1000); jQuery(this).hide().remove()"> &gt;&gt Bericht van 2008, klik om weer te geven. &lt;&lt;</a>
					<div id="filtered{$bericht.id}" class="verborgen">
				{/if}
				{$bericht.tekst|ubb}
				{if $bericht.bewerkt!=''}
					<div class="bewerkt clear">
						<hr />
						{$bericht.bewerkt|ubb}
					</div>
				{/if}
				{if $bericht.filtered}
					</div>
				{/if}
				</div>
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
			<a class="forumpostlink" id="laatste">Reageren</a>
		</td>
		<td class="forumtekst">
			{if $onderwerp->magToevoegen()}
				<form method="post" action="/communicatie/forum/toevoegen/{$onderwerp->getID()}" id="forumReageren">
					<fieldset>
						{* berichtje weergeven voor niet-ingeloggede gebruikers dat ze een naam moeten vermelden. *}
						{if $onderwerp->needsModeration()}
							<strong>Uw bericht wordt pas geplaatst nadat het bekeken en goedgekeurd is door de <a href="http://csrdelft.nl/actueel/groepen/Commissies/PubCie/">PubCie</a>.
							Het vermelden van <em>uw naam en email-adres</em> is verplicht.</strong> 
							<br /><br />
							<label for="email">Email-adres:</label><input type="text" name="email" /><br />
							{* spam trap, must be kept empty! *}
							<input type="text" name="firstname" value="" class="verborgen" />
						{* ingelogde gebruikers vertellen dat iedereen hun bericht mag lezen inclusief Google. *}
						{elseif $onderwerp->isOpenbaar()} 
							{* Openbaar forum: Iedereen mag dit lezen en zoekmachines nemen het op in hun zoekresultaten. *}
						{/if}
						<div id="berichtPreviewContainer" class="previewContainer"><div id="berichtPreview" class="preview"></div></div>
						<textarea name="bericht" id="forumBericht" class="forumBericht {if $onderwerp->isOpenbaar()}extern{/if}" rows="12">{$textarea}</textarea>
                        <div class="butn">
						    <a style="float: right; margin-right:0" class="handje knop" onclick="toggleDiv('ubbhulpverhaal')" title="Opmaakhulp weergeven">Opmaak</a>
						    <a style="float: right;" class="handje knop" onclick="vergrootTextarea('forumBericht', 10)" title="Vergroot het invoerveld"><strong>&uarr;&darr;</strong></a>

                            <input type="submit" name="submit" value="opslaan" id="forumOpslaan" />
						    <input type="button" value="voorbeeld" id="forumVoorbeeld" onclick="previewPost('forumBericht', 'berichtPreview')"/>
                        </div>
					</fieldset>
				</form>
			{/if}
			{$smarty.capture.magreageren}
		</td>
	</tr>
</table>
{* linkjes voor het forum nogeens weergeven, maar alleen als het aantal berichten in het onderwerp groter is dan 4 *}
{if $onderwerp->getSize()>4}
	{$smarty.capture.navlinks}
{/if}
