<table width = "100%" cellspacing="10px">
	<tr>
		<td width = "50%">
			<h2>Snel zoeken</h2>
			{$search}
			<br/>
			...of klik <a href="index.php?actie=theme">klik</a> hier voor een overzicht van alle onderwerpen
			<br/>
			<br/>
			<h2>De vormingsbank</h2><p>
			De mensheid heeft de afgelopen eeuwen enorm veel
ontdekt, ontwikkeld en ontgonnen. Voordat wijzelf in
staat zijn de volgende stappen te zetten op de snel-
weg van vooruitgang is het nodig een beeld te
vormen van het verleden en het heden. Wat weten
we al en wat vindt wie daarvan.</p><p>
Dat zijn de vragen die bij juischte toepassing, wat
zoveel betekent als een open edoch ook kritische
houding tenopzichte van, leiden tot vorming. Vorm-
ing, een prachtige begrip dat niets meer betekent dan
kennis vergaren. Iets dat we doen vanaf het allereer-
ste ogenblik, die momenten vlak voordat u in huilen
uitbarstte uit pure blijdschap het leven te hebben
ontvangen.</p><p>
Van wat er de afgelopen jaren binnen de Civitas
gebeurde op het gebied van die vorming, te weten de
geestelijke en de academische, vindt u in deze vorm-
ingsbank de resultaten. Vervolgens biedt deze digitale
omgeving u de mogelijkheid om interessante oude
koeien uit de sloot te halen om deze met nieuwe
bronnen en materialen opnieuw te belichten. Uiter-
aard wordt u niet tegengehouden als u zelf
haarscherp een nieuw onderwerp aansnijdt om deze
hier dan eens verfijnd te bediscussiëren.
</p><p>
Kortom, een digitale omgeving met bronnen[verzamelde en nieuwe materialen] en 
bijbehorende discussie, allen gedienstig in het spoor van
het grotere doel, gevormd worden, om zodadelijk
bepakt en bezakt, frank en fier de wereld verder te
ontdekken.</p>
		</td>
		<td>
				<div id="searchdiv" style="display:none">
					<h2>Zoekresultaten</h2><br/>
					<div id="quicksearchsearchresults"></div>
				</div>
					
				<div id="hoofdthemas">
					<h2>Hoofdonderwerpen</h2><br/>
					{section name=sec1 loop=$themes}
						<div class="thema-grotebalk">
							<table>
								<tr>
									<td><img class="plaatje" src="{$themes[sec1]->getImage()}"/></td>
									<td><div class="titel">
										<a  href="index.php?actie=subject&id={$themes[sec1]->id}" title="{$themes[sec1]->description}">
											{$themes[sec1]->name}
										</a>
									</div>
									<div class="bericht">{$themes[sec1]->description}</div>
									</td>
								</tr>
							</table>
						</div>
						{/section}
					<!-- use allowedit, not allowadd, to add a main theme -->
					{if $allowedit}
						<a href="index.php?actie=subject&id=0">Hoofdonderwerpen beheren</a>
					{/if}
				</div>
		</td>
	</tr>
</table>
				
