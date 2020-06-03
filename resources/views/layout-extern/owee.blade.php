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
				<img src="/images/owee2020.svg" alt="C.S.R. - Machtig Mooi">
			</div>
		</div>

		<div class="content pt-5 pb-5">
			<div class="row align-items-center">
				<div class="col-md-6 mb-4 mb-md-0">
					<h1>Word lid van C.S.R.</h1>
					<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras ac nisi vitae purus placerat finibus. Ut eros mauris, euismod eget ullamcorper non, aliquam facilisis nunc. Nulla porta congue nunc, eu lobortis lorem rhoncus sed. In id luctus risus, nec auctor nibh. Vivamus at nibh eget velit tincidunt finibus nec ac turpis.</p>

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
			<div class="content"></div>
		</div>

		<div class="content"></div>

		<div class="bbl atl interest">
			<div class="content"></div>
		</div>

		<div class="content">

		</div>

		<div class="bbl atl notes">
			<div class="content"></div>
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
