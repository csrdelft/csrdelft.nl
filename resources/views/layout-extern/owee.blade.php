@extends('layout-extern.layout')

@section('titel', 'Machtig mooi - OWee 2020')

@section('styles')
	@stylesheet('extern.css')
	@stylesheet('extern-owee.css')
	@script('extern-owee.js')
@endsection

@section('body')
	<!-- Banner -->
	<section id="banner">
		<div class="inner">
			<a href="/">
				<img src="/images/c.s.r.logo.svg" alt="Beeldmerk van de vereniging">
				<h1>C.S.R. Delft</h1>
			</a>
		</div>
	</section>

	<div class="owee">
		<div class="bbl atl hero">
			<div class="content">
				<img src="/images/owee/owee2020.svg" alt="C.S.R. - Machtig Mooi">
			</div>
		</div>

		<div class="content pt-5 pb-5">
			<div class="row align-items-center">
				<div class="col-md-6 mb-4 mb-md-0">
					<h1>Word lid van C.S.R.</h1>
					<p>C.S.R. Delft is de grootste christelijke vereniging van Delft. Lid zijn van onze studentenvereniging betekent voor jou dat je nieuwe vriendschappen maakt voor het leven en samen geniet van de activiteiten die de vereniging biedt. Het betekent dat je je geloof blijft voeden en je kan verdiepen met kringen, bidgroepjes en zangavonden. Lid zijn zorgt voor prachtige momenten tijdens je studententijd die je je leven lang niet gaat vergeten!</p>

					<div class="mt-4">
						<a href="#" class="cta primary">Ik wil lid worden</a>
						<a href="#" class="cta secondary">Eerst een lid spreken</a>
					</div>
				</div>
				<div class="col-md-6">
					<div class="iframe-container">
						<iframe src="https://www.youtube-nocookie.com/embed/AE8RE8e5qI4" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
					</div>
				</div>
			</div>
		</div>

		<div class="bbl atl buttons">
			<div class="content">
				<div class="row pt-4">
					<a class="col-6 col-md-4 mb-4" href="/vereniging/geloof">
						<img src="/images/owee/geloof.png" alt="Geloof">
						<span class="overlay">Geloof</span>
					</a>
					<a class="col-6 col-md-4 mb-4" href="/vereniging/vorming">
						<img src="/images/owee/vorming.png" alt="Vorming">
						<span class="overlay">Vorming</span>
					</a>
					<a class="col-6 col-md-4 mb-4" href="/vereniging/gezelligheid">
						<img src="/images/owee/gezelligheid.png" alt="Gezelligheid">
						<span class="overlay">Gezelligheid</span>
					</a>
					<a class="col-6 col-md-4 mb-4" href="/vereniging/sport">
						<img src="/images/owee/sport.png" alt="Sport">
						<span class="overlay">Sport</span>
					</a>
					<a class="col-6 col-md-4 mb-4" href="/vereniging/ontspanning">
						<img src="/images/owee/ontspanning.png" alt="Ontspanning">
						<span class="overlay">Ontspanning</span>
					</a>
					<a class="col-6 col-md-4 mb-4" href="/vereniging/societeit">
						<img src="/images/owee/societeit.png" alt="Societeit">
						<span class="overlay">Soci&euml;teit</span>
					</a>
				</div>
			</div>
		</div>

		<div class="content videos">
			<div class="row pt-4">
				<div class="col-12 mb-4">
					<h2>C.S.R. op YouTube</h2>
				</div>
				<div class="col-12 col-sm-6 col-md-4 mb-4">
					<div class="iframe-container">
						<iframe src="https://www.youtube-nocookie.com/embed/videoseries?list=PLXBOhyG24-WnNgg2RloapxC5X73J1Zxvi" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
					</div>
				</div>
				<div class="col-12 col-sm-6 col-md-4 mb-4">
					<div class="iframe-container">
						<iframe src="https://www.youtube-nocookie.com/embed/01kzRDhdcYw" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
					</div>
				</div>
				<a class="col-12 col-md-4 mb-4" href="https://www.youtube.com/user/CivitasFilms" target="_blank">
					<div class="iframe-container youtube-container">
						<div class="youtube">
							<div>
								<i class="fab fa-youtube"></i>
								<div>Bekijk meer op YouTube</div>
							</div>
						</div>
					</div>
				</a>
			</div>
		</div>

		<div class="bbl atl interest">
			<div class="content">
				<div class="pt-5 pt-sm-2">
					<h2>Interesse in C.S.R.?</h2>
					<p>Hieronder kan je je interesse aangeven door je gegevens achter te laten, wij houden je dan op de hoogte met nieuws over bijvoorbeeld open avonden of de OWee. Wanneer je al weet dat je lid wilt worden komend jaar, kan je hieronder ook je gegevens achterlaten voor je voorinschrijving.</p>
				</div>
			</div>
		</div>

		<div class="content pt-4 pb-4">
			<div class="row">
				<div class="col-md-5 col-lg-4">
					<a class="whatsapp" href="https://wa.me/31633271913" target="_blank">
						<i class="fab fa-whatsapp mr-3 mr-md-0"></i>
						<div class="call mt-3 mb-3">Vragen?<br>App met <br class="d-none d-md-inline">Maartje</div>
						<div class="cta">0633271913</div>
					</a>
				</div>
				<div class="col-md-7 col-lg-8">
					<div class="interesseformulier"></div>
				</div>
			</div>
		</div>

		<div class="bbl atl notes">
			<div class="content">Wanneer je lid wilt worden bij C.S.R. doorloop je een novitiaatsweek (ook wel de kennismakingstijd, afgekort KMT). Deze tijd zal plaatsvinden na de OWee (25 augustus t/m 29 augustus 2020), in deze week zal je elkaar, de vereniging en haar leden leren kennen. De activiteiten die tijdens de KMT worden ondernomen zullen op een respectvolle en veilige manier verlopen.</div>
		</div>
	</div>

	<!-- Footer -->
	<div id="wrapper">
		<section id="footer">
			<div class="inner">
				<ul class="copyright">
					<li>&copy; {{date('Y')}} - C.S.R. Delft - <a
							href="/download/Privacyverklaring%20C.S.R.%20Delft%20-%20Extern%20-%2025-05-2018.pdf">Privacy</a></li>
				</ul>
			</div>
		</section>
	</div>
@endsection
