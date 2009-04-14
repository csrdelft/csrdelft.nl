<h2>Profiel wijzigen</h2>
Hieronder kunt u uw eigen gegevens wijzigen. Voor enkele velden is het niet mogelijk zelf
wijzigingen door te voeren. Voor de meeste velden geldt daarnaast dat de ingevulde gegevens
een geldig formaat moeten hebben. Mochten er fouten in het gedeelte van uw profiel staan,
dat u niet zelf kunt wijzigen, meld het dan bij de <a href="mailto:vice-abactis@csrdelft.nl">Vice-Abactis</a>. <br /> <br />Als er
<span class="waarschuwing">tekst in rode letters</span> wordt afgebeeld bij een veld, dan
betekent dat dat de invoer niet geaccepteerd is, en dat u die zult moeten moeten aanpassen aan het
gevraagde formaat. Een aantal velden kan leeg gelaten worden als er geen zinvolle informatie voor is.


<form action="/communicatie/profiel/{$profiel->getUid()}/bewerken/" id="profielForm" method="post">
	{$profiel->getLid()->getPasfoto()}
	{foreach from=$profiel->getFields() item=field}
		{$field->view()}
	{/foreach}
	<div class="submit"><label for="submit">&nbsp;</label><input type="submit" value="opslaan" />
		<input type="reset" value="reset formulier" />
		<a class="knop" href="/communicatie/profiel/{$profiel->getUid()}">Annuleren</a>
	</div>
</form>
