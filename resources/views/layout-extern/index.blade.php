@extends('layout-extern.layout')

@section('titel', $titel)

@section('styles')
	@stylesheet('extern.css')
@endsection

@section('body')
	<!-- Banner -->
	<section id="banner">
		<div class="inner">
			<img src="/images/c.s.r.logo.svg" alt="Beeldmerk van de vereniging">
		</div>
		<h1 class="inner">
			C.S.R. Delft
		</h1>
	</section>

	<!-- Wrapper -->
	<section id="wrapper">

		<!-- One -->
		<section id="one" class="wrapper kleur1">
			<div class="inner">
				<span class="image"><img src="/images/vereniging.jpg" alt="Foto vereniging"/></span>
				<div class="content">
					<h2 class="major">C.S.R. Delft</h2>
					<p style="color:red;">
						<em>
							Vanwege de recente maatregelen omtrent de verspreiding van het coronavirus, is besloten per direct alle verenigingsactiviteiten af te lassen en is onze sociëteit gesloten. Als u hierover vragen heeft of het bestuur wil bereiken kunt u het bestuur mailen (bestuur@csrdelft.nl).<br>
							Heb je interesse om lid te worden bij C.S.R.? Stuur een appje/maak een praatje met Maartje (06 3327 1913) of mail promocie@csrdelft.nl.
						</em>
					</p>
					<p>De Civitas Studiosorum Reformatorum is een bruisende, actieve, christelijke studentenvereniging in
						Delft, rijk aan tradities die zijn ontstaan in haar 58-jarig bestaan. Het is een breed gezelschap
						van zo'n 270 leden met een zeer gevarieerde (kerkelijke) achtergrond, maar met een duidelijke
						eenheid door het christelijk geloof. C.S.R. is de plek waar al tientallen jaren studenten goede
						vrienden van elkaar worden, op intellectueel en geestelijk gebied groeien en goede studentengrappen
						uithalen.
					</p>
					<a href="/vereniging" class="special">Lees meer over C.S.R.</a>
				</div>
			</div>
		</section>

		<!-- Two -->
		<section id="two" class="wrapper alt kleur2">
			<div class="inner">
				<noscript class="lazy-load">
					<span class="image"><img src="/images/podium.jpg" alt="Sfeerfoto buiten"/></span>
				</noscript>
				<div class="content">
					<h2 class="major">C.S.R. in de OWee</h2>
					<p>Ter aanvang van elk studiejaar wordt er een OntvangstWeek georganiseerd, ofwel de OWee.
						Tijdens deze week is er de uitgelezen kans om kennis te maken met de universiteit en hogescholen,
						studieverenigingen, studentenverenigingen, sport- en cultuurcentrum, kerken en nog veel meer! Al
						deze groepen zullen zichzelf tijdens deze week op verschillende momenten en manieren presenteren.
						Ook bij C.S.R. stellen we van 17 t/m 20 augustus onze deuren open, dus kom vooral langs om de sfeer
						van C.S.R. en de rest van Delft te proeven!</p>
				</div>
			</div>
		</section>

		<!--
		<section id="two" class="wrapper-img">
			<img src="/images/vereniging.jpg"/>
		</section>
		-->

		<!-- Three -->
		<section id="three" class="wrapper kleur3">
			<div class="inner">
				<noscript class="lazy-load">
					<span class="image"><img src="/images/OC-2017.jpg" alt="Owee Commissie"/></span>
				</noscript>
				<div class="content">
					<h2 class="major">Interesse/vragen</h2>
					<p>Uiteraard ben je welkom om tijdens de OWee langs te komen op onze soci&euml;teit Confide aan de Oude Delft
						9. Ook is het mogelijk om dan in een C.S.R. huis te overnachten. Dit kun je aangeven tijdens het
						aanmelden voor de OWee op owee.nl.

						Buiten de OWee om is het eveneens mogelijk om bij C.S.R. langs te komen. Klik hieronder voor de
						mogelijkheden of voor het opvragen van een informatiepakket.</p>
					<a href="#footer" class="special">Vul interesseformulier in</a>
					<p class="lidworden">Wil je lid worden? Inschrijven kan in de OWee (17 t/m 20 augustus) in onze sociëteit. Zorg ervoor dat je de week van 24 t/m 30 augustus vrij houdt voor de novitiaatsweek.</p>
					<a href="/lidworden" class="special">Meer informatie over lid worden</a>
				</div>
			</div>
		</section>

		<!-- Four -->
		<section id="four" class="wrapper alt kleur4">
			<div class="inner">
				<div class="content">
					<h2 class="major">Foto's</h2>
					<noscript class="lazy-load">
						<div class="grid">
							<a class="lightbox-link" href="/fotoalbum/Publiek/Voorpagina/pic01.jpg"
								 data-lightbox="page-lightbox">
								<img class="bb-img" alt="" src="/fotoalbum/Publiek/Voorpagina/_resized/pic01.jpg"/>
							</a>
							<a class="lightbox-link" href="/fotoalbum/Publiek/Voorpagina/pic02.jpg"
								 data-lightbox="page-lightbox">
								<img class="bb-img" alt="/fotoalbum/Publiek/Voorpagina/pic02.jpg"
										 src="/fotoalbum/Publiek/Voorpagina/_resized/pic02.jpg">
							</a>
							<a class="lightbox-link" href="/fotoalbum/Publiek/Voorpagina/pic03.jpg"
								 data-lightbox="page-lightbox">
								<img class="bb-img" alt="/fotoalbum/Publiek/Voorpagina/pic03.jpg"
										 src="/fotoalbum/Publiek/Voorpagina/_resized/pic03.jpg">
							</a>
							<a class="lightbox-link" href="/fotoalbum/Publiek/Voorpagina/pic04.jpg"
								 data-lightbox="page-lightbox">
								<img class="bb-img" alt="/fotoalbum/Publiek/Voorpagina/pic04.jpg"
										 src="/fotoalbum/Publiek/Voorpagina/_resized/pic04.jpg">
							</a>
							<a class="lightbox-link" href="/fotoalbum/Publiek/Voorpagina/pic05.jpg"
								 data-lightbox="page-lightbox">
								<img class="bb-img" alt="/fotoalbum/Publiek/Voorpagina/pic05.jpg"
										 src="/fotoalbum/Publiek/Voorpagina/_resized/pic05.jpg">
							</a>
							<a class="lightbox-link" href="/fotoalbum/Publiek/Voorpagina/pic06.jpg"
								 data-lightbox="page-lightbox">
								<img class="bb-img" alt="/fotoalbum/Publiek/Voorpagina/pic06.jpg"
										 src="/fotoalbum/Publiek/Voorpagina/_resized/pic06.jpg">
							</a>
							<a class="lightbox-link" href="/fotoalbum/Publiek/Voorpagina/pic07.jpg"
								 data-lightbox="page-lightbox">
								<img class="bb-img" alt="/fotoalbum/Publiek/Voorpagina/pic07.jpg"
										 src="/fotoalbum/Publiek/Voorpagina/_resized/pic07.jpg">
							</a>
							<a class="lightbox-link" href="/fotoalbum/Publiek/Voorpagina/pic08.jpg"
								 data-lightbox="page-lightbox">
								<img class="bb-img" alt="/fotoalbum/Publiek/Voorpagina/pic08.jpg"
										 src="/fotoalbum/Publiek/Voorpagina/_resized/pic08.jpg">
							</a>
							<a class="lightbox-link" href="/fotoalbum/Publiek/Voorpagina/pic09.jpg"
								 data-lightbox="page-lightbox">
								<img class="bb-img" alt="/fotoalbum/Publiek/Voorpagina/pic09.jpg"
										 src="/fotoalbum/Publiek/Voorpagina/_resized/pic09.jpg">
							</a>
						</div>
					</noscript>
				</div>
			</div>
		</section>


		<!-- Footer -->
		<section id="footer">
			<div class="inner">
				<h2 class="major">Interesseformulier</h2>
				@include('layout-extern.form')
				<ul class="contact">
					<li class="fa-home">
						Soci&euml;teit Confide <br/>
						Oude Delft 9<br/>
						2611 BA Delft
					</li>
					<li class="fa-phone">06-19470413</li>
					<li class="fa-envelope"><a href="mailto:{{env('EMAIL_ABACTIS')}}">{{env('EMAIL_ABACTIS')}}</a></li>
					<li class="fa-facebook"><a href="https://www.facebook.com/OWee-CSR-Delft-1570871723180451/?fref=ts">Like
							de Facebookpagina om de laatste updates te ontvangen</a></li>
					<li class="fa-map-marker">
						<noscript class="lazy-load">
							<iframe title="Confide op Google Maps" height="300" frameborder="0" style="border:0"
								src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2456.0445350385166!2d4.360246300000008!3d52.0060664!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47c5b5c03dabb5b3%3A0xea6a437036970629!2sOude+Delft+9!5e0!3m2!1sen!2s!4v1404470858468">
							</iframe>
						</noscript>
					</li>
				</ul>
				<noscript class="lazy-load">
					<ul class="sponsors">
						<li>
							<a href="https://www.dosign.com/nl-nl/carriere/download-dosign-students-app/?utm_source=Banner%20Students&utm_medium=sv%20C.S.R.&utm_campaign=Promotie%20app ">
								<img src='https://csrdelft.nl/plaetjes/banners/dosign.jpg' alt='Dosign advertentie'>
							</a>
						</li>
						<li>
							<a href="https://www.allekabels.nl/">
								<img width=40% src='https://csrdelft.nl/plaetjes/banners/allekabels.png' alt='Alle kabels'>
							</a>
						</li>
						<li>
							<a href="http://mechdes.nl/">
								<img src='https://csrdelft.nl/plaetjes/banners/mechdes.gif' alt='Mechdes advertentie'>
							</a>
						</li>
						<li>
							<a href="http://galjemadetachering.nl/">
								<img src='https://csrdelft.nl/plaetjes/banners/galjema_banner.jpg' alt='Galjema advertentie'>
							</a>
						</li>
						<li>
							<a href="https://www.pricewise.nl/energie-vergelijken/">
								<img width=25% src="https://csrdelft.nl/plaetjes/banners/pricewise.svg" alt="Pricewise">
							</a>
						</li>
						<li>
							<a href="https://www.hoyhoy.nl/autoverzekering/">
								<img src='https://csrdelft.nl/plaetjes/banners/logo_hoyhoy.png' alt='Autoverzekering - hoyhoy'/>
							</a>
						</li>
						<li>
							<a href="https://www.geld.nl/">
								<img src="https://csrdelft.nl/plaetjes/banners/geld.png"
										 alt="Geld.nl Vergelijk website">
							</a>
						</li>
						<li>
							<a href="https://www.goedkoopsteautoverzekering.net/">
								<img src="https://csrdelft.nl/plaetjes/banners/goedkoopste-autoverzekering.png"
										 alt="Goedkoopste autoverzekering">
							</a>
						</li>
						<li>
							<a href="https://www.allianzdirect.nl/autoverzekering/">
								<img width=60% src='https://csrdelft.nl/plaetjes/banners/allianzdirect.jpg' alt='Allianz Direct'>
							</a>
						</li>
						<li>
							<a href="http://www.tudelft.nl/">
								<img src="https://csrdelft.nl/plaetjes/banners/TU_Delft_logo_White.png"
										 alt="TUDelft">
							</a>
						</li>
					</ul>
				</noscript>
				<ul class="copyright">
					<li>&copy; {{date('Y')}} - C.S.R. Delft - <a
							href="/download/Privacyverklaring%20C.S.R.%20Delft%20-%20Extern%20-%2025-05-2018.pdf">Privacy</a></li>
				</ul>
			</div>
		</section>

	</section>
@endsection
