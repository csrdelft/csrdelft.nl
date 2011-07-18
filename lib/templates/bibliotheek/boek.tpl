{* 
 *	Boek weergeven
 *}

<div class="foutje">{$melding}</div>
{if $boek->magVerwijderen()}
	<a class="boek verwijderen" href="/communicatie/bibliotheek/verwijderboek/{$boek->getID()}" title="Boek verwijderen" onclick="confirm('Weet u zeker dat u dit boek wilt verwijderen')">{icon get="verwijderen"}</a>
{/if}


<div class="boek" id="{$boek->getID()}">
	<div class="blok">
		<h1><div class="titel">Boek</div>	
		<div class="bewerk" id="titel">
			<span id="waarde_titel" class="text">{$boek->getTitel()|escape:'html'}</span>
			<input type="text" maxlength="100" value="{$boek->getTitel()|escape:'html'}" class="editbox" id="waarde_input_titel" /> 
		</div></h1>
	</div>
	<div class="blok gegevens">
		<div class="label">Auteur</div>		
		<div class="bewerk" id="auteur">
			<span id="waarde_auteur" class="text">{$boek->getAuteur()|escape:'html'}&nbsp;</span>
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
		{if $boek->isCSRboek()}
			<div class="label">Biebcode</div>
			<div class="bewerk" id="code">
				<span id="waarde_code" class="text">{$boek->getCode()|escape:'html'}&nbsp;</span>
				<input type="text" maxlength="100" value="{$boek->getCode()|escape:'html'}" class="editbox" id="waarde_input_code" /> 
			</div>
		{/if}
		<div class="label">Rubriek</div>
		<div class="bewerk" id="rubriek">
			<span id="waarde_rubriek" class="text">{$boek->getRubriek()|escape:'html'}&nbsp;</span>
			<input type="text" maxlength="100" value="{$boek->getRubriek()|escape:'html'}" class="editbox" id="waarde_input_rubriek" /> 
		</div>
	</div>
</div>







