{*
 *	Boek weergeven
 *}

<div class="foutje">{getMelding()}</div>
<ul class="horizontal">
	<li>
		<a href="/bibliotheek/" title="Naar de catalogus">Catalogus</a>
	</li>
	<li>
		<a href="/bibliotheek/wenslijst" title="Wenslijst van bibliothecaris">Wenslijst</a>
	</li>
	{toegang P_BIEB_READ}
		<li>
			<a href="/bibliotheek/rubrieken" title="Rubriekenoverzicht">Rubrieken</a>
		</li>
	{/toegang}
</ul>

{if $boek->magBekijken()}
	{* blok rechts met knopjes *}
	<div class="controls">
		<a class="btn" href="/bibliotheek/boek">{icon get="book_add"} Nieuw boek</a>
		{if $boek->getId()!=0}
			{if $boek->magVerwijderen()}
				<a class="btn post verwijderen" href="/bibliotheek/verwijderboek/{$boek->getId()}" title="Boek verwijderen" onclick="return confirm('Weet u zeker dat u dit boek wilt verwijderen?')">{icon get="verwijderen"} Verwijderen</a>
			{/if}
			<a class="btn post ReloadPage" href="/bibliotheek/addexemplaar/{$boek->getId()}" title="Ik bezit dit boek ook" onclick="return confirm('U bezit zelf een exemplaar van dit boek? Door het toevoegen aan de catalogus geef je aan dat anderen dit boek kunnen lenen.')">{icon get="user_add"} Ik bezit dit boek</a>
			{toegang P_BIEB_MOD}
				<a class="btn post ReloadPage" href="/bibliotheek/addexemplaar/{$boek->getId()}/x222" title="C.S.R.-bieb bezit dit boek ook" onclick="return confirm('Bezit de C.S.R.-bieb een exemplaar van dit boek?')">{icon get="user_add"} Is een biebboek</a>
			{/toegang}
		{/if}
	</div>
{/if}



{* nieuw boek formulier *}
{if $boek->getId()==0}
	<h1>Nieuw boek toevoegen</h1>
	<p>Zoek via het Google Books-zoekveld je boek en kies een van de suggesties om de boekgegevens hieronder in te vullen.</p>
	<div class="boekzoeker" title="Geef titel, auteur, isbn of een ander kenmerk van het boek. Minstens 7 tekens, na 1 seconde verschijnen suggesties.">
		<label for="boekzoeker"><img src="/plaetjes/knopjes/google.ico" width="16" height="16" alt="Zoeken op Google Books" />Google Books:</label><input type="text" placeholder="Zoek en kies een suggestie om de velden te vullen" id="boekzoeker">
	</div>

	{$boekFormulier->view()}


{else}
	{* weergave bestaand boek, soms met bewerkbare velden *}
	<div class="boek" id="{$boek->getId()}">

		{if $boekFormulier->hasFields()}

			{$boekFormulier->view()}

		{else}

			<div class="blok header boekgegevens">
				<div class="regel"><label>Boek</label><span>{$boek->getTitel()}</span></div>
			</div>
			<div class="blok gegevens boekgegevens">
				{if $boek->getAuteur()!=''}<div class="regel"><label>Auteur</label>{$boek->getAuteur()}</div>{/if}
				{if $boek->getPaginas()!=0}<div class="regel"><label>Pagina's</label>{$boek->getPaginas()}</div>{/if}
				{if $boek->getTaal()!=''}<div class="regel"><label>Taal</label>{$boek->getTaal()}</div>{/if}
				{if $boek->getISBN()!=''}<div class="regel"><label>ISBN</label>{$boek->getISBN()}</div>{/if}
				{if $boek->getUitgeverij()!=''}<div class="regel"><label>Uitgeverij</label>{$boek->getUitgeverij()}</div>{/if}
				{if $boek->getUitgavejaar()!=0}<div class="regel"><label>Uitgavejaar</label>{$boek->getUitgavejaar()}</div>{/if}
			</div>
			<div class="blok gegevens boekgegevens">
				<div class="regel"><label>Rubriek</label>{$boek->getRubriek()}</div>
				{if $boek->getCode()!='' AND $boek->isBiebboek()}<div class="regel"><label>Code</label>{$boek->getCode()}</div>{/if}
			</div>
		{/if}

		<div class="clear-left"></div>

	</div>

	{* Exemplaren *}
	<div class="row">
	{assign var=total_exemplaren_bibliotheek value=0} {* teller nodig om in compacte weergave slechts 1 biebboek te laten zien. *}
	<div class="blok gegevens exemplaren col-md-6" id="exemplaren">
		<h3>Exemplaren</h3>
		{foreach from=$boek->getExemplaren() item=exemplaar name=exemplaren}
			<div class="exemplaar uitgebreid {if $smarty.foreach.exemplaren.total>4 AND !$exemplaar->isEigenaar() AND ($exemplaar->eigenaar_uid!='x222' OR $total_exemplaren_bibliotheek>0 )}verborgen{/if}">
				<div class="fotolabel">{CsrDelft\model\ProfielModel::getLink($exemplaar->eigenaar_uid, 'pasfoto')}</div>
				<div class="gegevensexemplaar" id="ex{$exemplaar->id}">
				{* eigenaar *}
					<div class="regel">
						<label>Eigenaar</label>
						{if $exemplaar->eigenaar_uid=='x222'}{assign var=total_exemplaren_bibliotheek value=$total_exemplaren_bibliotheek+1}
							C.S.R.-bibliotheek
						{else}
							{CsrDelft\model\ProfielModel::getLink($exemplaar->eigenaar_uid, 'civitas')}
						{/if}
					</div>

				{* opmerking *}
					{if $exemplaar->magBewerken()}
						{$exemplaarFormulieren[$exemplaar->id]->view()}
					{else}
						{if $exemplaar->opmerking != ''}
							<div class="regel">
								<label>Opmerking</label><span class="opmerking">{$exemplaar->opmerking|escape:'html'}</span>
							</div>
						{/if}
					{/if}
				{* status *}
					<div class="regel">
						<label>Status</label>
						{if $exemplaar->getStatus()=='uitgeleend'}
							<span title="Sinds {$exemplaar->uitleendatum|reldate|strip_tags}">Uitgeleend aan {CsrDelft\model\ProfielModel::getLink($exemplaar->uitgeleend_uid, 'civitas')}</span>
						{elseif $exemplaar->getStatus()=='teruggegeven'}
							<span title="Was uitgeleend sinds {$exemplaar->uitleendatum|reldate|strip_tags}">Teruggegeven door {CsrDelft\model\ProfielModel::getLink($exemplaar->uitgeleend_uid, 'civitas')}</span>
						{elseif $exemplaar->getStatus()=='vermist'}
							<span class="waarschuwing" title="Sinds {$exemplaar->uitleendatum|reldate|strip_tags}">Vermist</span>
						{elseif $exemplaar->getStatus()=='beschikbaar' }
							Beschikbaar
						{/if}
					</div>
					{if $exemplaar->getStatus()=='beschikbaar' AND $boek->isEigenaar($exemplaar->id)}
						{$boek->ajaxformuliervelden->findByName("lener_`$exemplaar->id`")->view()}
					{/if}
				{* actieknoppen *}
					<div class="regel actieknoppen">
						<label>&nbsp;</label>
						<div>
							{if $exemplaar->getStatus()=='beschikbaar'}
								{if $exemplaar->eigenaar_uid=='x222'} {* bibliothecaris werkt met kaartjes *}
									{if !$boek->isEigenaar($exemplaar->id)} {* BASFCie hoeft opmerking niet te zien *}
										<span class="suggestie recht">Biebboek lenen: laat het kaartje achter voor de bibliothecaris.</span><br />
									{/if}
								{else}
									<a class="btn post ReloadPage" href="/bibliotheek/exemplaarlenen/{$exemplaar->id}" title="Leen dit boek" onclick="return confirm('U wilt dit boek van {CsrDelft\model\ProfielModel::getNaam($exemplaar->eigenaar_uid)} lenen?')">{icon get="lorry"} Exemplaar lenen</a>
								{/if}
							{elseif $exemplaar->status=='uitgeleend' AND CsrDelft\model\security\LoginModel::getUid()==$exemplaar->uitgeleend_uid AND $exemplaar->uitgeleend_uid!=$exemplaar->eigenaar_uid}
								<a class="btn post ReloadPage" href="/bibliotheek/exemplaarteruggegeven/{$exemplaar->id}" title="Boek heb ik teruggegeven" onclick="return confirm('U heeft dit exemplaar van {CsrDelft\model\ProfielModel::getNaam($exemplaar->eigenaar_uid)} teruggegeven?')">{icon get="lorry_go"} Teruggegeven</a>
							{/if}
							{if $exemplaar->isEigenaar()}
								{if ($exemplaar->getStatus()=='uitgeleend' OR $exemplaar->isTeruggegeven())}
									<a class="btn post ReloadPage" href="/bibliotheek/exemplaarterugontvangen/{$exemplaar->id}" title="Boek is ontvangen" onclick="return confirm('Dit exemplaar van {CsrDelft\model\ProfielModel::getNaam($exemplaar->eigenaar_uid)} is terugontvangen?')">{icon get="lorry_flatbed"} Ontvangen</a>
								{elseif $exemplaar->getStatus()=='beschikbaar'}
									<a class="btn post ReloadPage" href="/bibliotheek/exemplaarvermist/{$exemplaar->id}" title="Exemplaar is vermist" onclick="return confirm('Is het exemplaar van {CsrDelft\model\ProfielModel::getNaam($exemplaar->eigenaar_uid)} vermist?')">{icon get="emoticon_unhappy"} Vermist</a>
								{elseif $exemplaar->getStatus()=='vermist'}
									<a class="btn post ReloadPage" href="/bibliotheek/exemplaargevonden/{$exemplaar->id}" title="Exemplaar teruggevonden" onclick="return confirm('Is het exemplaar van {CsrDelft\model\ProfielModel::getNaam($exemplaar->eigenaar_uid)} teruggevonden?')">{icon get="emoticon_smile"} Teruggevonden</a>
								{/if}
								<a class="btn post ReloadPage" href="/bibliotheek/verwijderexemplaar/{$exemplaar->id}" title="Exemplaar verwijderen" onclick="return confirm('Weet u zeker dat u dit exemplaar van {CsrDelft\model\ProfielModel::getNaam($exemplaar->eigenaar_uid)} wilt verwijderen?')">{icon get="verwijderen"} Verwijderen</a>
							{/if}
						</div>
					</div>
				</div>
			</div>
		{foreachelse}
			<p>Geen exemplaren.</p>
		{/foreach}

			{* compacte weergave met alleen foto's *}
			{assign var=total_exemplaren_bibliotheek value=0} {* teller nodig om in compacte weergave slechts 1 biebboek te laten zien. *}
			{if $smarty.foreach.exemplaren.total>4}
				<div class="exemplaar compact">
					{foreach from=$boek->getExemplaren() item=exemplaar}
						{if !$boek->isEigenaar($exemplaar->id) AND ($exemplaar->eigenaar_uid!='x222' OR $total_exemplaren_bibliotheek>0 )}
							{CsrDelft\model\ProfielModel::getLink($exemplaar->eigenaar_uid, 'pasfoto')}
							<div class="statusmarkering"><span class="biebindicator {$exemplaar->status}" title="Boek is {$exemplaar->status}">• </span></div>
						{/if}
						{if $exemplaar->eigenaar_uid=='x222'}
							{assign var=total_exemplaren_bibliotheek value=$total_exemplaren_bibliotheek+1}
						{/if}
					{/foreach}
					<br /><div class="clear"></div>
					<label>&nbsp;</label><a onclick="jQuery(this).parent().parent().children('div.exemplaar->uitgebreid').show(); jQuery(this).parent().remove();">» meer informatie</a>
				</div>
			{/if}
		</div>

	{* beschrijvingen *}

	<div class="beschrijvingen col-md-6">
		<h3 class="header">Recensies en beschrijvingen</h3>
			<table id="beschrijvingentabel">
			{foreach from=$recensies item=beschrijving}

				<tr><td class="linkerkolom"></td><td style="width:506px"></td></tr>
				<tr >
						<td class="linkerkolom recensist">
							<span class="recensist">{CsrDelft\model\ProfielModel::getLink($beschrijving->schrijver_uid, 'civitas')}</span><br />
							<span class="moment">{$beschrijving->toegevoegd|reldate}</span><br />

						{* knopjes bij elke post *}
							{if $beschrijving->magVerwijderen()}
								{knop url="/bibliotheek/verwijderbeschrijving/`$boek->getId()`/{CsrDelft\model\security\LoginModel::getUid()}" class='post ReloadPage' type=verwijderen confirm='Weet u zeker dat u deze beschrijving wilt verwijderen?'}
							{/if}
						</td>
						<td class="beschrijving b{cycle values="0,1"}" id="beschrijving{$beschrijving->id}">
							{if $recensieFormulier->getModel() == $beschrijving}
								{$recensieFormulier->view()}
							{else}
								{$beschrijving->beschrijving|bbcode}
							{/if}
							{if $beschrijving->bewerkdatum!='0000-00-00 00:00:00'}
								<br /><span class="offtopic">Bewerkt {$beschrijving->bewerkdatum|reldate}</span>
							{/if}
						</td>
				</tr>
				<tr>
					<td class="linkerkolom"></td><td class="tussenschot"></td>
				</tr>
			{/foreach}
				{if $recensieFormulier->isNieuw() }
				<tr>
					<td>
						{$recensieFormulier->view()}
					</td>
				</tr>
				{/if}
			</table>
	</div>
</div>
{/if}
