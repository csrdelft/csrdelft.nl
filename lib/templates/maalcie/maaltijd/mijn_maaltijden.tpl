{*
mijn_maaltijden.tpl	|	P.W.G. Brussee (brussee@live.nl)
*}
<p>
	Op deze pagina kunt u zich aan- en afmelden voor maaltijden op Confide door op de knop te klikken in de kolom "Aangemeld".
	Onderstaande tabel toont de maaltijden in de komende maand.
	De kolom "Aangemeld" geeft aan of u bent aangemeld voor de maaltijd met "Ja", of niet bent aangemeld met "Nee".
	Als u een abonnement heeft dat u automatisch heeft aangemeld staat er "(abo)" achter.
	Als u bent ingedeeld voor een corveetaak staat dat aangegeven door middel van dit icoon: {icon get="paintcan" title="Corvee"}
	Als u (kwali)kok bent heeft u tevens de mogelijkheid de maaltijdlijst te bekijken en de inschrijvingen te sluiten.
</p>
<h3>Gasten aanmelden</h3>
<p>
	Als u staat ingeschreven voor een maaltijd, kunt u op uw naam gasten aanmelden voor de maaltijd.
	Vul in het vak 'gasten' het aantal in door erop te klikken.
	Ook kunt u een opmerking maken over eventuele allergiën van uw gast(en).
	Dit kan alleen als de maaltijd nog niet is gesloten.
	Daarna moet u contact opnemen met de koks!
</p>
<p>
	N.B. De maaltijd sluit op de dag van de maaltijd rond 15:00 (wanneer de koks de lijst met aanmeldingen uitprinten).
	Vanaf dat moment zal deze ketzer u niet meer willen aan- of afmelden en bent u aangewezen op persoonlijk contact met de koks.
</p>
<table id="maalcie-tabel" class="maalcie-tabel">
	<thead>
		<tr>
			<th>Wanneer</th>
			<th>Omschrijving</th>
			<th>Eters (Limiet)</th>
			<th style="width: 140px;">Aangemeld</th>
			<th>Gasten</th>
			<th title="Gasten allergie/diëet" style="width: 25px;">{icon get="comment"}</th>
		</tr>
	</thead>
	<tbody>
		{foreach from=$maaltijden key=mid item=maaltijd}
			{include file='maalcie/maaltijd/mijn_maaltijd_lijst.tpl' aanmelding=$aanmeldingen.$mid}
		{/foreach}
	</tbody>
</table>

<h3>Maaltijd beoordelen</h3>
Om inzicht te krijgen in de kwantiteit en kwaliteit van de maaltijden kunt u hier uw terugkoppeling geven over de recente maaltijden die u bezocht heeft.
<table id="beoordelingen-tabel" class="maalcie-tabel">
	<thead>
		<tr>
			<th>Wanneer</th>
			<th>Omschrijving</th>
			<th>Kwantiteit</th>
			<th>Kwaliteit</th>
		</tr>
	</thead>
	<tbody>
		{foreach from=$beoordelen key=mid item=maaltijd}
			<tr>
				<td>{$maaltijd->datum|date_format:"%a %e %b"}</td>
				<td>{$maaltijd->titel}</td>
				<td>{$kwantiteit.$mid->view()}</td>
				<td>{$kwaliteit.$mid->view()}</td>
			</tr>
		{/foreach}
	</tbody>
</table>