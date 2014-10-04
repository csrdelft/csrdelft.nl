{getMelding()}

{if $actie=='novietBewerken'}
	<h2>Noviet toevoegen</h2>
	<p>
		Welkom bij C.S.R.! Hieronder mag je gegevens invullen in het databeest van de Civitas. Zo kunnen we contact met je houden,
		kunnen andere leden opzoeken waar je woont en kun je (na het novitiaat) op het forum berichten plaatsen.
	</p>
{else}
	<h2>Profiel wijzigen</h2>
	<p class="profielBewerken">
		Hieronder kunt u uw eigen gegevens wijzigen. Voor enkele velden is het niet mogelijk zelf
		wijzigingen door te voeren. Voor de meeste velden geldt daarnaast dat de ingevulde gegevens
		een geldig formaat moeten hebben. Mochten er fouten in het gedeelte van uw profiel staan,
		dat u niet zelf kunt wijzigen, meld het dan bij de <a href="mailto:vice-abactis@csrdelft.nl">Vice-Abactis</a>.
	</p>
{/if}
<p>
	Als er <span class="waarschuwing">tekst in rode letters</span> wordt afgebeeld bij een veld, dan
	betekent dat dat de invoer niet geaccepteerd is, en dat u die zult moeten moeten aanpassen aan het
	gevraagde formaat. Een aantal velden kan leeg gelaten worden als er geen zinvolle informatie voor is.
</p>

{* het formulier *}
{$profiel->getFormulier()->view()}
	
