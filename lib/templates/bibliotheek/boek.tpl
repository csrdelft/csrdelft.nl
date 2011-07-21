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
{if $boek->getID()==0}
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
{if $boek->getID()!=0}
	<div class="boek" id="{$boek->getID()}">
		<div class="blok header">
			<h1><div class="titel">Boek</div>	
			<div class="bewerk titelwaarde" id="titel">
				<span id="waarde_titel" class="text">{if $boek->getTitel()==''}<span class="suggestie">Geef de titel...</span>{else}{$boek->getTitel()|escape:'html'}{/if}&nbsp;</span>
				<input type="text" maxlength="100" value="{$boek->getTitel()|escape:'html'}" class="editbox" id="waarde_input_titel" /> 
			</div></h1>&nbsp;
		</div>
		<div class="blok gegevens">
			<div class="label">Auteur</div>		
			<div class="bewerk" id="auteur">
				<span id="waarde_auteur" class="text">{if $boek->getAuteur()==''}<span class="suggestie">Achternaam, V.L. van de</span>{else}{$boek->getAuteur()|escape:'html'}{/if}&nbsp;</span>
				<input type="text" maxlength="100" value="{$boek->getAuteur()|escape:'html'}" class="editbox" id="waarde_input_auteur" /> 
			</div>
			<div class="label">Pagina's</div>	
			<div class="bewerk" id="paginas">
				<span id="waarde_paginas" class="text">{$boek->getPaginas()|escape:'html'}&nbsp;</span>
				<input type="text" maxlength="100" value="{$boek->getPaginas()|escape:'html'}" class="editbox" id="waarde_input_paginas" /> 
			</div>
			<div class="label">Taal</div>		
			<div class="bewerk" id="taal">
				<span id="waarde_taal" class="text">{$boek->getTaal()|escape:'html'}&nbsp;</span>
				<input type="text" maxlength="100" value="{$boek->getTaal()|escape:'html'}" class="editbox" id="waarde_input_taal" /> 
			</div>
			<div class="label">ISBN</div>		
			<div class="bewerk" id="isbn">
				<span id="waarde_isbn" class="text">{$boek->getISBN()|escape:'html'}&nbsp;</span>
				<input type="text" maxlength="100" value="{$boek->getISBN()|escape:'html'}" class="editbox" id="waarde_input_isbn" /> 
			</div>
			<div class="label">Uitgeverij</div>	
			<div class="bewerk" id="uitgeverij">
				<span id="waarde_uitgeverij" class="text">{$boek->getUitgeverij()|escape:'html'}&nbsp;</span>
				<input type="text" maxlength="100" value="{$boek->getUitgeverij()|escape:'html'}" class="editbox" id="waarde_input_uitgeverij" /> 
			</div>
			<div class="label">Uitgavejaar</div>
			<div class="bewerk" id="uitgavejaar">
				<span id="waarde_uitgavejaar" class="text">{$boek->getUitgavejaar()|escape:'html'} </span>
				<input type="text" maxlength="100" value="{$boek->getUitgavejaar()|escape:'html'}" class="editbox" id="waarde_input_uitgavejaar" /> 
			</div>
		</div>
		<div class="blok gegevens">
			<div class="label">Rubriek</div>
			<div class="bewerk" id="rubriek">
				<span id="waarde_rubriek" class="text">{$boek->getRubriek()|escape:'html'}&nbsp;</span>
				<input type="text" maxlength="100" value="{$boek->getRubriek()|escape:'html'}" class="editbox" id="waarde_input_rubriek" /> 
			</div>
			{if $boek->isCSRboek()}
				<div class="label">Biebcode</div>
				<div class="bewerk" id="code">
					<span id="waarde_code" class="text">{if $boek->getCode()=='' AND $boek->getRubriek()==''}<span class="suggestie">Vul eerst de rubriek in</span>{else}{$boek->getCode()|escape:'html'}{/if}&nbsp;</span>
					<input type="text" maxlength="100" value="{$boek->getCode()|escape:'html'}" class="editbox" id="waarde_input_code" /> 
				</div>
			{/if}
		</div>
	</div>

	{* blok rechts met knopjes *}
	<div class="controls boekacties">	
		{if $boek->magVerwijderen()}
			<a class="knop" href="/communicatie/bibliotheek/verwijderboek/{$boek->getID()}" title="Boek verwijderen" onclick="confirm('Weet u zeker dat u dit boek wilt verwijderen')">{icon get="verwijderen"}verwijderen</a><br />
		{/if}
		<a class="knop" href="/communicatie/bibliotheek/bezitboek/{$boek->getID()}" title="Ik bezit dit boek ook" onclick="confirm('Weet u zeker dat u dit boek wilt verwijderen')">{icon get="user_add"}ik bezit dit boek</a>
	</div><div style="clear: left;"></div>


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
							{knop url="/communicatie/bibliotheek/bewerkbeschrijving/`$boek->getID()`/`$beschrijving.id`" type=bewerken}
						{/if}
						{if $boek->magVerwijderen($beschrijving.id)}
							{knop url="/communicatie/bibliotheek/verwijderbeschrijving/`$boek->getID()`/`$beschrijving.id`" type=verwijderen confirm='Weet u zeker dat u deze beschrijving wilt verwijderen?'}
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
		<form action="/communicatie/bibliotheek/{if $action=='bewerken'}bewerkbeschrijving/{$boek->getID()}/{$boek->getBeschrijvingsId()}{else}addbeschrijving/{$boek->getID()}{/if}" id="addBeschrijving" class="boekForm" method="post">
			{foreach from=$boek->getFields('beschrijving') item=field}
				{$field->view()}
			{/foreach}
			<div class="submit"><label for="submit">&nbsp;</label><input type="submit" value="opslaan" />
				<input type="reset" value="reset" />
			</div>
		</form>
	</div>
{/if}






