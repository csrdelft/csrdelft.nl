{* 
 *	Boek weergeven
 *}

<div class="foutje">{$melding}</div>
<ul class="horizontal">
	<li>
		<a href="/communicatie/bibliotheek/" title="Naar de catalogus">Catalogus</a>
	</li>
	<li>
		<a href="/communicatie/bibliotheek/boekstatus" title="Uitgebreide boekstatus">Boekstatus</a>
	</li>
	<li>
		<a href="/communicatie/bibliotheek/wenslijst" title="Wenslijst van bibliothecaris">Wenslijst</a>
	</li>
</ul>

{if $boek->magBekijken()}
	<div class="controls">
		<a class="knop" href="/communicatie/bibliotheek/nieuwboek" title="Nieuw boek toevoegen">{icon get="book_add"} Boek toevoegen</a>
	</div>
{/if}

{* nieuw boek formulier *}
{if $boek->getId()==0}
	<h1>Nieuw boek toevoegen</h1>
	<p>Vul onderstaande velden. </p>
	<form action="/communicatie/bibliotheek/nieuwboek/0" id="boekaddForm" class="boekForm" method="post">
		{foreach from=$boek->getFields('nieuwboek') item=field}
			{$field->view()}
		{/foreach}
		<div class="submit"><label for="submit">&nbsp;</label><input type="submit" value="opslaan" />
			<input type="reset" value="reset formulier" />
			<a class="knop" href="/communicatie/bibliotheek/">Annuleren</a>
		</div>
	</form>
{else}
{* weergave boek, met bewerkbare velden *}
	<div class="boek" id="{$boek->getId()}">
		<div class="blok header">
			{$boek->getField('titel')->view()}
		</div>
		<div class="blok gegevens">
			{$boek->getField('auteur')->view()}
			{$boek->getField('paginas')->view()}
			{$boek->getField('taal')->view()}
			{$boek->getField('isbn')->view()}
			{$boek->getField('uitgeverij')->view()}
			{$boek->getField('uitgavejaar')->view()}
		</div>
		<div class="blok gegevens">
			{$boek->getField('rubriek')->view()}
			{$boek->getField('code')->view()}
		</div>
	</div>


	{* blok rechts met knopjes *}
	<div class="controls boekacties">	
		<br /><br />
		{if $boek->magVerwijderen()}
			<a class="knop verwijderen" href="/communicatie/bibliotheek/verwijderboek/{$boek->getId()}" title="Boek verwijderen" onclick="return confirm('Weet u zeker dat u dit boek wilt verwijderen?')">{icon get="verwijderen"} Verwijderen</a><br />
		{/if}
		<a class="knop" href="/communicatie/bibliotheek/addexemplaar/{$boek->getId()}" title="Ik bezit dit boek ook" onclick="return confirm('U bezit zelf een exemplaar van dit boek?')">{icon get="user_add"} Ik bezit dit boek</a>
		{if $boek->isBASFCie()}
			<a class="knop" href="/communicatie/bibliotheek/addexemplaar/{$boek->getId()}/x222" title="C.S.R.-bieb bezit dit boek ook" onclick="return confirm('De C.S.R.-bieb bezit een exemplaar van dit boek?')">{icon get="user_add"} Is een biebboek</a>
		{/if}
	</div><div style="clear: left;"></div>

	{* Exemplaren *}
	<div class="exemplaren" id="exemplaren">
		<div class="blok gegevens">
			<h2>Exemplaren</h2>
			{foreach from=$boek->getExemplaren() item=exemplaar}
				<div class="exemplaar">
					<div class="label">{$exemplaar.eigenaar_uid|pasfoto}</div>		
					<div class="gegevensexemplaar" id="ex{$exemplaar.id}">
					{* eigenaar *}
						<label>Eigenaar</label>
						{if $exemplaar.eigenaar_uid=='x222'}
							C.S.R.-bibliotheek
						{else}
							{$exemplaar.eigenaar_uid|csrnaam:'civitas'}
						{/if}
					{* opmerking *}
						{if $boek->isEigenaar($exemplaar.id)}
							{$boek->getField("opmerking_`$exemplaar.id`")->view()}
						{else}
							{if $exemplaar.opmerking != ''}
							<br /><label>Opmerking</label>{$exemplaar.opmerking|escape:'html'}
							{/if}
							<br />
						{/if}
					{* status *}
						<label>Status</label>
						{if $exemplaar.status=='uitgeleend'}
							<span title="Sinds {$exemplaar.uitleendatum|reldate|strip_tags}">Uitgeleend aan {$exemplaar.uitgeleend_uid|csrnaam:'civitas'}</span><br />
						{/if}
						{if $exemplaar.status=='teruggegeven'}
							<span title="Was uitgeleend sinds {$exemplaar.uitleendatum|reldate|strip_tags}">Teruggegeven door {$exemplaar.uitgeleend_uid|csrnaam:'civitas'}</span><br />
						{/if}
						{if $exemplaar.status=='vermist'}
							<span class="melding" title="Sinds {$exemplaar.uitleendatum|reldate|strip_tags}">Vermist</span><br />
						{/if}
						{if $exemplaar.status=='beschikbaar' }
							Beschikbaar<br />
							{if $boek->isEigenaar($exemplaar.id)}
								<div class="uitleenveld">
									<form action="/communicatie/bibliotheek/exemplaarlenen/{$boek->getId()}/{$exemplaar.id}/ander" id="lener_{$exemplaar.id}" class="lenerForm" method="post">
										{$boek->getField("lener_`$exemplaar.id`")->view()}
										<input type="hidden" value="lener_{$exemplaar.id}" name="id"/>
										<div class="submitt">
											&nbsp;<input type="submit" value="Opslaan" />
										</div>
									</form>
								</div>
							{/if}
						{/if}
					{* actieknoppen *}
						<label>&nbsp;</label><div class="actieknoppen">
							{if $exemplaar.status=='beschikbaar'}
								{if $exemplaar.eigenaar_uid=='x222'} {* bibliothecaris werkt met kaartjes *}
									{if !$boek->isEigenaar($exemplaar.id)} {* basfcie hoeft opmerking niet te zien *}
										<span class="suggestie" style="font-style: normal;">Biebboek lenen: laat het kaartje achter voor de bibliothecaris.</span><br />
									{/if}
								{else}
									<a class="knop" href="/communicatie/bibliotheek/exemplaarlenen/{$boek->getId()}/{$exemplaar.id}" title="Leen dit boek" onclick="return confirm('U wilt dit boek van {$exemplaar.eigenaar_uid|csrnaam:'civitas':'plain'} lenen?')">{icon get="lorry"} Exemplaar lenen</a>
								{/if}
							{/if}
							{if $exemplaar.status=='uitgeleend' AND $loginlid->getUid()==$exemplaar.uitgeleend_uid AND $exemplaar.uitgeleend_uid!=$exemplaar.eigenaar_uid}
								<a class="knop" href="/communicatie/bibliotheek/exemplaarteruggegeven/{$boek->getId()}/{$exemplaar.id}" title="Boek heb ik teruggegeven" onclick="return confirm('U heeft dit exemplaar van {$exemplaar.eigenaar_uid|csrnaam:'civitas':'plain'} teruggegeven?')">{icon get="lorry_go"} Teruggegeven</a>
							{/if}
							{if ($exemplaar.status=='uitgeleend' OR $exemplaar.status=='teruggegeven') AND $boek->isEigenaar($exemplaar.id)}
								<a class="knop" href="/communicatie/bibliotheek/exemplaarterugontvangen/{$boek->getId()}/{$exemplaar.id}" title="Boek is ontvangen" onclick="return confirm('Dit exemplaar van {$exemplaar.eigenaar_uid|csrnaam:'civitas':'plain'} is terugontvangen?')">{icon get="lorry_flatbed"} Ontvangen</a>
							{/if}
							{if $exemplaar.status=='beschikbaar' AND $boek->isEigenaar($exemplaar.id)}
								<a class="knop" href="/communicatie/bibliotheek/exemplaarvermist/{$boek->getId()}/{$exemplaar.id}" title="Exemplaar is vermist" onclick="return confirm('Is het exemplaar van {$exemplaar.eigenaar_uid|csrnaam:'civitas':'plain'} vermist?')">{icon get="emoticon_unhappy"} Vermist</a>
							{/if}
							{if $exemplaar.status=='vermist' AND  $boek->isEigenaar($exemplaar.id)}
								<a class="knop" href="/communicatie/bibliotheek/exemplaargevonden/{$boek->getId()}/{$exemplaar.id}" title="Exemplaar teruggevonden" onclick="return confirm('Is het exemplaar van {$exemplaar.eigenaar_uid|csrnaam:'civitas':'plain'} teruggevonden?')">{icon get="emoticon_smile"} Teruggevonden</a>
							{/if}
							{if $boek->isEigenaar($exemplaar.id)}
								<a class="knop" href="/communicatie/bibliotheek/verwijderexemplaar/{$boek->getId()}/{$exemplaar.id}" title="Exemplaar verwijderen" onclick="return confirm('Weet u zeker dat u dit exemplaar van {$exemplaar.eigenaar_uid|csrnaam:'civitas':'plain'} wilt verwijderen?')">{icon get="verwijderen"} Verwijderen</a>
							{/if}
						</div>
					</div>
				</div>
			{foreachelse}
				<p>Geen exemplaren.</p>
			{/foreach}
			
		</div>
	</div>

	{* beschrijvingen *}
	<div class="beschrijvingen">
		<h2 class="header">Recensies en beschrijvingen</h2>
		{if $boek->countBeschrijvingen()>0}
			<table id="beschrijvingentabel">
			{foreach from=$boek->getBeschrijvingen() item=beschrijving}
				<tr >
					<td class="linkerkolom recensist {if $action=='bewerken' AND $boek->getBeschrijvingsId()==$beschrijving.id}bewerken{/if}">
						{$beschrijving.schrijver_uid|csrnaam:'user'}<br />
						<span class="moment">{$beschrijving.toegevoegd|reldate}</span><br />

					{* knopjes bij elke post *}	
						{if $boek->magBewerken($beschrijving.id)}
							{knop url="/communicatie/bibliotheek/bewerkbeschrijving/`$boek->getId()`/`$beschrijving.id`" type=bewerken}
						{/if}
						{if $boek->magVerwijderen($beschrijving.id)}
							{knop url="/communicatie/bibliotheek/verwijderbeschrijving/`$boek->getId()`/`$beschrijving.id`" type=verwijderen confirm='Weet u zeker dat u deze beschrijving wilt verwijderen?'}
						{/if}
					</td>
					<td class="beschrijving b{cycle values="0,1"}{if $action=='bewerken' AND $boek->getBeschrijvingsId()==$beschrijving.id} bewerken{/if}" id="beschrijving{$beschrijving.id}">
						{if $action=='bewerken' AND $boek->getBeschrijvingsId()==$beschrijving.id} <span class='bewerken'>Deze beschrijving wordt bewerkt</span><br /><br />{/if}
						{$beschrijving.beschrijving|ubb}
						{if $beschrijving.bewerkdatum!='0000-00-00 00:00:00'}
							<br /><div class="offtopic">Bewerkt {$beschrijving.bewerkdatum|reldate}</div>
						{/if}
					</td>
				</tr>
				<tr>
					<td class="linkerkolom"></td><td class="tussenschot"></td>
				</tr>
			{/foreach}
			</table>
		{else}
			<p class="header">Nog geen beschrijvingen.</p>
		{/if}

		{* formulier voor beschrijvingen    *}
		<form action="/communicatie/bibliotheek/{if $action=='bewerken'}bewerkbeschrijving/{$boek->getId()}/{$boek->getBeschrijvingsId()}{else}addbeschrijving/{$boek->getId()}{/if}" id="addBeschrijving" class="boekForm" method="post">
			{foreach from=$boek->getFields('beschrijving') item=field}
				{$field->view()}
			{/foreach}
			<div class="submit"><label for="submit">&nbsp;</label><input type="submit" value="opslaan" />
				<input type="reset" value="reset" />
			</div>
		</form>
	</div>
{/if}
