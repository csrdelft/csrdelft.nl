{* 
 *	Boek weergeven
 *}

<div class="foutje">{$melding}</div>
<a href="/communicatie/bibliotheek/" title="Naar de catalogus">Catalogus</a>
{if $boek->magBekijken()}
	<div class="controls">
		<a class="knop" href="/communicatie/bibliotheek/nieuwboek">{icon get="book_add"} Toevoegen</a>
	</div>
{/if}
<br />

{* nieuw boek formulier *}
{if $boek->getId()==0}
	<h1>Nieuw boek toevoegen</h1>
	<p>Vul onderstaande velden</p>
	<form action="/communicatie/bibliotheek/nieuwboek/0" id="boekaddForm" class="boekForm" method="post">
		{foreach from=$boek->getFields('nieuwboek') item=field}
			{$field->view()}
		{/foreach}
		<div class="submit"><label for="submit">&nbsp;</label><input type="submit" value="opslaan" />
			<input type="reset" value="reset formulier" />
			<a class="knop" href="/communicatie/bibliotheek/">Annuleren</a>
		</div>
	</form>
{/if}

{* weergave boek, met bewerkbare velden *}
{if $boek->getId()!=0}
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
			{if $boek->isCSRboek()}
				{$boek->getField('code')->view()}
			{/if}
		</div>
	</div>


	{* blok rechts met knopjes *}
	<div class="controls boekacties">	
		<br /><br />
		{if $boek->magVerwijderen()}
			<a class="knop verwijderen" href="/communicatie/bibliotheek/verwijderboek/{$boek->getId()}" title="Boek verwijderen" onclick="return confirm('Weet u zeker dat u dit boek wilt verwijderen?')">{icon get="verwijderen"} Verwijderen</a><br />
		{/if}
		<a class="knop" href="/communicatie/bibliotheek/addexemplaar/{$boek->getId()}" title="Ik bezit dit boek ook" onclick="return confirm('U bezit zelf een exemplaar van dit boek?')">{icon get="user_add"} Ik bezit dit boek</a>
	</div><div style="clear: left;"></div>

	{* Exemplaren *}
	<div class="exemplaren" >
		<div class="blok gegevens">
			<div class="label">.</div><h2>Exemplaren</h2>
			{foreach from=$boek->getExemplaren() item=exemplaar}
				<div class="regel">
					<div class="label">{$exemplaar.eigenaar_uid|pasfoto}</div>		
					<div class="exemplaar" id="ex{$exemplaar.id}">
						{if $exemplaar.eigenaar_uid=='x222'}C.S.R.-bibliotheek{else}{$exemplaar.eigenaar_uid|csrnaam:'civitas'}{/if}<br />
						{if $exemplaar.status=='uitgeleend'}
							Uitgeleend aan {$exemplaar.uitgeleend_uid|csrnaam:'civitas'}<br />
						{/if}
						{if $exemplaar.status=='teruggegeven'}
							Teruggegeven door {$exemplaar.uitgeleend_uid|csrnaam:'civitas'}<br />
						{/if}
						{if $exemplaar.status=='beschikbaar'}
							<a class="knop" href="/communicatie/bibliotheek/exemplaarlenen/{$boek->getId()}/{$exemplaar.id}" title="Leen dit boek" onclick="return confirm('U wilt dit boek van {$exemplaar.eigenaar_uid|csrnaam:'civitas':'plain'} lenen?')">{icon get="lorry"} Lenen</a>
						{/if}
						{if $exemplaar.status=='uitgeleend' AND $loginlid->getUid()==$exemplaar.uitgeleend_uid}
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
			{foreachelse}
				<p>Geen exemplaren.</p>
			{/foreach}
			
		</div>
	</div>

	{* beschrijvingen *}
	<div class="beschrijvingen">
		<h2>Recensies en beschrijvingen</h2>
		{if $boek->countBeschrijvingen()>0}
			<table id="beschrijvingentabel">
			{foreach from=$boek->getBeschrijvingen() item=beschrijving}
				<tr class="{if $action=='bewerken' AND $boek->getBeschrijvingsId()==$beschrijving.id}bewerken{/if}">
					<td class="recensist">
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
						{$beschrijving.beschrijving|ubb}
						{if $beschrijving.bewerkdatum!='0000-00-00 00:00:00'}
							<br /><div class="offtopic">Bewerkt {$beschrijving.bewerkdatum|reldate}</div>
						{/if}
					</td>
				</tr>
				<tr>
				<td class="recensist"></td><td class="tussenschot"></td>
				</tr>
			{/foreach}
			</table>
		{else}
			<p>Nog geen beschrijvingen.</p>
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






