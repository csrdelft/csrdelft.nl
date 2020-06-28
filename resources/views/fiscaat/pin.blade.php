@extends('fiscaat.base')

@section('titel', 'Pinbestellingen')

@section('civisaldocontent')
	<h2>Pin transacties beheer</h2>
	<p>
		Iedere nacht rond een uur of één worden de transacties van de pinautomaat opgehaald en
		vergeleken met de bestellingen die in ons systeem staan. Als er iets is mis gegaan komt dat
		in deze tabel terecht.
	</p>
	<p>
		Als een bestelling en transactie aan elkaar gekoppeld moeten worden kun je een selectie maken,
		door ctrl ingedrukt te houden kun je een selectie maken van meerdere regels.
	</p>
	<p>De verwerk knop doet één van de volgende dingen:</p>
	<ul>
		<li>
			Als er alleen een transactie is: Op basis van een lidnummer een nieuwe
			bestelling maken.
		</li>
		<li>
			Als er alleen een bestelling is: De bestelling aanpassen zodat er niet meer
			gepind is. Dit laat ook een commentaar achter op de bestelling.
		</li>
		<li>
			Als er een transactie en een bestelling is, maar de bedragen kloppen niet: Het bedrag
			goed zetten op de bestelling. Dit laat ook een commentaar achter op de bestelling.
		</li>
	</ul>
	<p>
		De negeer knop haalt een bestelling, transactie of match uit de lijst met fouten. Dit kan
		bijvoorbeeld gebruikt worden bij een feest, een corrigerende bestelling of een gesplitste
		betaling of bestelling.
	</p>
	<p>
		De heroverweeg knop controleert gekoppelde matches opnieuw, gebruik dit als je aanpassingen
		hebt gemaakt in het SocCie systeem.
	</p>

	<style>
		.dt-buttons {
			position: sticky !important;
			top: 58px;
		}
	</style>

	{!! $table->toString() !!}
@endsection
