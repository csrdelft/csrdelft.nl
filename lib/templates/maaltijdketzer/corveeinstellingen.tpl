{assign var='actief' value='corveeinstellingen'}
{include file='maaltijdketzer/menu.tpl'}
{$melding}


<h1>Corveeinstellingen</h1>

<form action="/actueel/maaltijden/corveeinstellingen" id="instellingenForm" class="instellingenForm" method="post">
	{foreach from=$instellingen->getFields() item=field}
		{$field->view()}
	{/foreach}
	<div class="submit">
		<label for="submit">&nbsp;</label><input type="submit" value="opslaan" />
	</div>
</form><br/>



<h1>Corveejaarresetter</h1>
<p>
	{*Met deze tool verwijder je alle corveetaken t/m de ingevulde datum. *}
	Vul einddatum van de vorige corveeperiode in en controleer of alle punten zijn toegekend. 
	Als alle toekenningen naar wens zijn, kunt u het vorige corveejaar resetten.<br/> 
	Reset omvat: 
	<ul>
		{*<li>Alle corveetaken t/m datum worden verwijderd.</li>*}
		<li>Hertelling: NieuwPuntentotaal = Corveepunten + bonus + ceil(teBehalenCorveepunten * %Vrijstelling) - teBehalenCorveepunten.</li>
		<li>Bonus op nul zetten.</li>
	</ul>
</p>
<form action="/tools/corveeresetter.php" id="resetForm" class="resetForm" method="post">
	<fieldset>
		<label>Eind vorige corveeperiode</label>{$datumveld}<br/>
		<div id="controleContainer">
			<label for="submit">&nbsp;</label><input type="button" name="submit" id="submit" value="Corveepuntentoekenning controleren" onclick="corveeResetter('controleren'); return false;" />
		</div>		
		<div  id="resetContainer" class="verborgen"></div>
	</fieldset>
</div>
